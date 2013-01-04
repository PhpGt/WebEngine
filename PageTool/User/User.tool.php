<?php class User_PageTool extends PageTool {
/**
 * User PageTool is used to provide authentication within your application, 
 * along with anonymous users for applications that don't require signing up but
 * do require persistent storage. Anonymous users can then be converted into
 * full users by regular authentication.
 *
 * auth() is called to start the authentication process.
 * unauth() is called to clear any authentication data.
 * checkAuth() is called to check if the user is authenticated.
 *
 * If authentication is not required in your application, 
 * getUser() is called to get reference to a database user, authenticated or
 * simply anonymous. This is a less-strict version of checkAuth().
 */

private $_domainWhiteList = array();

public function go($api, $dom, $template, $tool) {}

public function __get($name) {
	switch($name) {
	case "id":
		return $_SESSION["PhpGt_User"]["Id"];
		break;
	case "username":
	case "userName":
		return $_SESSION["PhpGt_User"]["Username"];
		break;
	case "uuid":
		return $_COOKIE["PhpGt_Login"][0];
		break;
	case "firstName":
	case "firstname":
		$this->checkNames();
		return $_SESSION["PhpGt_User"]["FirstName"];
		break;
	case "lastName":
	case "lastname":
		$this->checkNames();
		return $_SESSION["PhpGt_User"]["LastName"];
		break;
	case "fullName":
	case "fullname":
		$this->checkNames();
		return $this->firstName . " " . $this->lastName;
		break;
	default:
		// If non-standard property is requested, check in database for
		// the field.
		$name = ucfirst($name);
		$dbUser = $this->_api["User"]->getById(["Id" => $this->id]);
		return $dbUser[$name];
		break;
	}
}

/**
 * Checks for a tracking cookie, and if it doesn't exist, creates one.
 * @return string The tracking UUID.
 */
public function track() {
	if(empty($_COOKIE["PhpGt_Track"])) {
		$anonId = $this->generateSalt();
		$expires = strtotime("+52 weeks");
		if(setcookie("PhpGt_Track", $anonId, $expires, "/") === false) {
			throw new HttpError(500,
				"Error generating tracking cookie in User PageTool.");
		}
		return $anonId;
	}

	return $_COOKIE["PhpGt_Track"];
}

/**
 * Obtains all stored user details in an associative array.
 * @return array All known user details.
 */
public function get() {
	return $_SESSION["PhpGt_User"];
}

/**
 * Begins the authentication process using the given provider. Valid providers
 * are OAuth providers including: "Google", "Facebook", "MyOpenId".
 * @param  string $method The authentication provider.
 * @return bool           True if the user successfully authenticates.
 */
public function auth($method = "Google") {
	$oid = new OpenId_Utility($method);
	$username = $oid->getData();
	// TODO: If not in white list, display plain white PHP.Gt message
	// on error 403 page. Provide mechanism to use different account.
	if(!$this->checkWhiteList($username) 
	|| empty($username)) {
		$this->unAuth();
		return false;
	}
	$this->setAuthData($username);
	return true;
}

/**
 * Unauthenticates any logged in user and removes any cookies set.
 * @param  string $forwardTo Where to forward the user after unauthenticating.
 */
public function unAuth($forwardTo = "/") {
	unset($_SESSION["PhpGt_User.tool_AuthData"]);
	unset($_SESSION["PhpGt_User"]);
	$this->deleteCookies();
	header("Location: " . $forwardTo);
	return;
}

/**
 * Used like checkAuth(), but doesn't require authentication. GetUser can be 
 * used to allow anonymous users to use the application, and be treated as
 * usual users in terms of database storage and returned values.
 * If the UUID doesn't exist in the database, a new anonymous user will be
 * created.
 * @param  string $uuid The UUID to use, taken from the tracking cookie.
 * @return array        The user details, authenticated or anonymous.
 */
public function getUser($uuid) {
	// TODO.
}

/**
 * Applications can set white lists of domains to allow logging in through
 * OAuth/OpenID. Email addresses outside of this list will not be allowed
 * access to the application.
 *
 * @param Array|string An array of domains, regular expression that matches 
 * multiple domains, or a single domain to add to the white list.
 */
public function addWhiteList($whiteList) {
	$whiteListArray = array();

	if(is_array($whiteList)) {
		$whiteListArray = $whiteList;
	}
	else if(is_string($whiteList)) {
		// A single domain provided.
		$whiteListArray[] = $whiteList;
	}

	$this->_domainWhiteList = array_merge(
		$this->_domainWhiteList, $whiteListArray);
}

/**
 * Checks to see if the given username is allowed to authenticate to the 
 * application according to the optional whitelist.
 * @param  string $username Full username (email)
 * @return bool             True if the given username fits the optional 
 * whitelist parameters.
 */
public function checkWhiteList($username) {
	// If there is no whitelist, allow all.
	if(empty($this->_domainWhiteList)) {
		$this->addWhiteList("*");
	}

	$result = false;

	foreach ($this->_domainWhiteList as $white) {
		if (preg_match("/^\/.*\/[a-zA-Z]?$/", $white)) {
			// Whitelist is a RegEx (preg_match returns 0 on no match, but 
			// false on error - note !==).
			if(preg_match($white, $username) > 0) {
				$result = true;
			}
		}
		else if(is_string($white)) {
			if(fnmatch($white, $username)) {
				$result = true;
			}
		}
	}
	return $result;
}

/**
 * FakeAuth allows development to continue while offline. Using openId
 * requires an internet connection, so adding a special button in
 * development releases allows users to authenticate (with no real 
 * authentication happening). Simply pass a username to this function to
 * fully authenticate the username as if it were authenticated with OpenId.
 *
 * @param string $username The username to authenticate.
 * @return bool True on success (which will allways occur).
 */
public function fakeAuth($username) {
	$this->setAuthData($username);
	return true;
}

/**
 * Used internally after a successful authentication to store the details in a
 * server-side session.
 */
private function setAuthData($username) {
	$_SESSION["PhpGt_User.tool_AuthData"] = $username;

	$uuid = hash("sha512", $username);
	$userSalt = $this->generateSalt();
	$expires = strtotime("+2 weeks");
	setcookie(
		"PhpGt_Login[0]",
		$uuid,
		$expires,
		"/");
	setcookie(
		"PhpGt_Login[1]",
		$userSalt,
		$expires,
		"/");
	setcookie(
		"PhpGt_Login[2]",
		hash("sha512", $uuid . $userSalt . APPSALT),
		$expires,
		"/");
}

/**
 * Checks the current session for authentication data. This may be
 * authentication with OpenId or using a simple username/password stored
 * in the User database table.
 * @return mixed False if there is no authentication data, or an associative
 * array containing all known attributes about the user.
 */
public function checkAuth() {
	$userId = null;
	$username = null;
	
	// The openid_identity variable is sent by an OpenId provider on
	// successful authentication.
	if(isset($_GET["openid_identity"])) {
		$this->auth();
		if(isset($_GET["openid_return_to"])) {
			header("Location: " . $_GET["openid_return_to"]);
			exit;
		}
	}
	
	// User auth through OpenId or saved cookie is stored in the session.
	if(isset($_SESSION["PhpGt_User.tool_AuthData"])) {
		$username = $_SESSION["PhpGt_User.tool_AuthData"];
		// User has authenticated, need to match to database.
		// The database user may not exist and need creating.
		$dbUser = $this->_api["User"]->get(["Username" => $username]);
		if(is_null($dbUser) || $dbUser->hasResult) {
			// Already exists - use this user.
			$userId = $dbUser["Id"];
		}
		else {
			// Doesn't already exist - create and use new user.
			$newDbUser = $this->_api["User"]->addEmpty(array(
				"Username" => $username,
				"Uuid" => hash("sha512", $username)
			));
			$userId = $newDbUser->lastInsertId;
		}
	}
	else {
		// Cookie information:
		// Login 0 = sha512 of username (the user's UUID)
		// Login 1 = sha512 unique salt, generated for this user only
		// Login 2 = sha512 authentication hash sha(login0.login1.site_salt)
		// The site salt is stored as a named constant, defined in security
		// config.
		if(isset($_COOKIE["PhpGt_Login"])) {
			if(isset($_COOKIE["PhpGt_Login"][0])
			&& isset($_COOKIE["PhpGt_Login"][1])
			&& isset($_COOKIE["PhpGt_Login"][2])) {
				$userHash = $_COOKIE["PhpGt_Login"][0];
				$saltHash = $_COOKIE["PhpGt_Login"][1];
				$authHash = $_COOKIE["PhpGt_Login"][2];
				
				// Find the user from their UUID.
				$dbResult = $this->_api["User"]->getByUuid(array(
					"Uuid" => $userHash
				));
				if($dbResult->hasResult) {
					// Check authenticity of cookies before logging user in.
					$authHash2 = hash("sha512",
						$userHash . $saltHash . APPSALT);
					if($authHash == $authHash2) {
						// Log in.
						$dbUser = $this->_api["User"]->getByUuid(array(
							"Uuid" => $userHash
						));
						if($dbUser->hasResult) {
							// Already exists - use this user.
							$userId = $dbUser["Id"];
							$_SESSION["PhpGt_User.tool_AuthData"] = 
								$dbUser["Username"];
							$this->refreshCookies();
						}
						else {
							throw new HttpError(500, 
								"User PageTool detected proper cookie auth, "
								. "but no user detected in database.");
						}
					}
					else {
						// Failure - kill all cookies.
						$this->deleteCookies();
					}
				}
			}
			else {
				// Kill any trace of the login cookie.
				$this->deleteCookies();
			}
		}

		if(isset($_SESSION["PhpGt_User"])) {
			if(empty($_SESSION["PhpGt_User"])) {
				unset($_SESSION["PhpGt_User"]);
				return false;
			}
		}
	}

	if(!is_null($userId)) {
		$_SESSION["PhpGt_User"] = [
			"Id" => $userId,
			"Username" => $_SESSION["PhpGt_User.tool_AuthData"]
		];
	}

	if(empty($_SESSION["PhpGt_User"])) {
		return false;
	}
	return $_SESSION["PhpGt_User"];
}

/**
 * Attempts to retrieve extra data from the database associated to the current
 * user Id, for using with the __get magic method.
 */
private function checkNames() {
	if(empty($_SESSION["PhpGt_User"]["FirstName"])
	|| empty($_SESSION["PhpGt_User"]["LastName"])) {
		if(empty($_SESSION["PhpGt_User"]["Id"])) {
			throw new HttpError(500, "User PageTool error finding User Id.");
		}
		$user = $this->_api["User"]->getById(
			["Id" => $_SESSION["PhpGt_User"]["Id"]]);

		$_SESSION["PhpGt_User"]["FirstName"] = $user["FirstName"];
		$_SESSION["PhpGt_User"]["LastName"] = $user["LastName"];
	}
}

/**
 * Every time there is user activity, refresh the cookies to keep them alive.
 */
private function refreshCookies() {
	$expires = strtotime("+52 weeks");
	setcookie(
		"PhpGt_Login[0]",
		$_COOKIE["PhpGt_Login"][0],
		$expires,
		"/");
	setcookie(
		"PhpGt_Login[1]",
		$_COOKIE["PhpGt_Login"][1],
		$expires,
		"/");
	setcookie(
		"PhpGt_Login[2]",
		$_COOKIE["PhpGt_Login"][2],
		$expires,
		"/");
}

/**
 * Unsets all cookies used by the PageTool.
 */
private function deleteCookies() {
	setcookie("PhpGt_Login[0]", "deleted", 0, "/");
	setcookie("PhpGt_Login[1]", "deleted", 0, "/");
	setcookie("PhpGt_Login[2]", "deleted", 0, "/");
	unset($_COOKIE["PhpGt_Login"]);
}

/**
 * Creates a UUID for tracking anonymous users.
 * @return string The UUID.
 */
private function generateSalt() {
	// TODO: A real salt function.
	return hash("sha512", rand(0, 10000));
}

}?>