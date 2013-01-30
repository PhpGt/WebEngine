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

public function go($api, $dom, $template, $tool) {}

public function __get($name) {
	if(empty($_SESSION["PhpGt_User"])) {
		$user = $this->getUser();
	}
	else {
		$user = $_SESSION["PhpGt_User"];
	}

	switch($name) {
	case "authenticated":
	case "isAuthenticated":
		return !empty($user["username"]);
		break;
	case "id":
		return $user["ID"];
		break;
	case "username":
	case "userName":
		return $user["username"];
		break;
	case "uuid":
		return empty($_COOKIE["PhpGt_Login"])
			? $_COOKIE["PhpGt_Track"]
			: $_COOKIE["PhpGt_Login"][0];
		break;
	case "orphanedID":
		return empty($user["orphanedID"])
			? null
			: $user["orphanedID"];
	default:
		return null;
		break;
	}
}

/**
 * Used like checkAuth(), but doesn't require authentication. GetUser can be 
 * used to allow anonymous users to use the application, and be treated as
 * usual users in terms of database storage and returned values.
 * If the UUID doesn't exist in the database, a new anonymous user will be
 * created.
 * @param  string $uuid Optional UUID to use, if not provided it is taken from
 * the tracking cookie.
 * @return array        The user details, identified or anonymous.
 */
public function getUser($uuid = null) {
	// Ensure there is a UUID tracking cookie set.
	if(is_null($uuid)) {
		$uuid = $this->track();
	}

	// If user is authenticated already, return early with the authenticated
	// user detains in an array.
	if($this->checkAuth()) {
		$this->setActive();
		return $_SESSION["PhpGt_User"];
	}
	
	// Ensure there is a related user in the database.
	// If a user doesn't exist, create one.
	$db = $this->_api[$this];
	$dbUser = $db->getByUuid(["uuid" => $uuid]);

	if($dbUser->hasResult) {
		$isIdentified = $dbUser["User_Type__name"] !== "Anon";
		$dbUser->setData("isIdentified", $isIdentified);
		$dbUser = $dbUser->result[0];
	}
	else {
		$result = $db->addAnon(["uuid" => $uuid]);
		// Build an array that matches what is stored in the database.
		$dbUser = array(
			"ID" => $result->lastInsertId,
			"uuid" => $uuid,
			"User_Type__name" => "Anon",
			"isIdentified" => false
		);
	}

	$this->setActive($dbUser["ID"]);

	// Return the array, or array-like-object representing the user.
	return $dbUser;
}

/**
 * Checks for a tracking cookie, and if it doesn't exist, creates one.
 * @param  $force Optional. Pass in a new uuid to track with.
 * @return string The tracking UUID.
 */
public function track($forceUuid = null) {
	if(empty($_COOKIE["PhpGt_Track"]) || !is_null($forceUuid)) {
		$uuid = is_null($forceUuid)
			? $this->generateSalt()
			: $forceUuid;
		$expires = strtotime("+105 weeks");
		if(setcookie("PhpGt_Track", $uuid, $expires, "/") === false) {
			throw new HttpError(500,
				"Error generating tracking cookie in User PageTool.");
		}
		$_COOKIE["PhpGt_Track"] = $uuid;
		$cookie = $_COOKIE["PhpGt_Track"];
		return $uuid;
	}

	return $_COOKIE["PhpGt_Track"];
}

/**
 * Checks the login cookies for authentication, completing any OpenId auth
 * requests that are still active, and instantiates the PhpGt_User session
 * object.
 * @return bool|array False if not authenticated, array of user details if 
 * authenticated.
 */
public function checkAuth() {
	// The openid_identity variable is sent by an OpenId provider on 
	// successful authentication.
	$this->checkOpenId();

	// The PhpGt_User.tool_AuthData session key is used for OAuth, cookie or 
	// other login mechanisms to store an authenticated username, for full
	// authentication in this function.
	if(isset($_SESSION["PhpGt_User.tool_AuthData"])) {
		$username = $_SESSION["PhpGt_User.tool_AuthData"];

		// User has authenticated in some way, and a username is known.
		// Need to match to a database user, which may not exist yet.
		$dbUser = $this->_api[$this]->get(["username" => $username]);
		if($dbUser->hasResult) {
			// User already stored in database.
			return $this->userSession($dbUser, $_COOKIE["PhpGt_Track"]);
		}
		else {
			// Authenticated user doesn't exist in database, but there may be
			// an anonymous user in the database with same UUID.
			$anonDbUser = $this->_api[$this]->getByUuid(
				["uuid" => $_COOKIE["PhpGt_Track"]]);
			if($anonDbUser->hasResult) {
				// Upgrade the anon user to full user.
				$oldUuid = $_COOKIE["PhpGt_Track"];
				$uuid = $this->track($_COOKIE["PhpGt_Login"][0]);

				$this->_api[$this]->anonIdentify([
					"uuid" => $oldUuid,
					"newUuid" => $uuid,
					"username" => $username
				]);
				return $this->userSession($uuid);
			}
			else {
				// Create a new fresh user with current UUID.
				$uuid = empty($_COOKIE["PhpGt_Login"])
					? $_COOKIE["PhpGt_Track"]
					: $_COOKIE["PhpGt_Login"][0];
				$newDbUser = $this->_api[$this]->addEmpty([
					"username" => $username,
					"uuid" => $uuid
				]);
				return $this->userSession($newDbUser->lastInsertId);
			}
		}

		// Code can't rech this point - must have returned user object by now.
	}

	// Cookie information:
	// Login 0 = sha512 of username (the user's UUID)
	// Login 1 = sha512 unique salt, generated for this user only
	// Login 2 = sha512 authentication hash sha(login0.login1.site_salt)
	// The site salt is stored as a named constant, defined in security config.
	
	if(isset($_COOKIE["PhpGt_Login"])) {
		// There is a login cookie, as described above.
		if(isset($_COOKIE["PhpGt_Login"][0])
		&& isset($_COOKIE["PhpGt_Login"][1])
		&& isset($_COOKIE["PhpGt_Login"][2])) {
			$userHash = $_COOKIE["PhpGt_Login"][0];
			$saltHash = $_COOKIE["PhpGt_Login"][1];
			$authHash = $_COOKIE["PhpGt_Login"][2];

			// Find the user from their UUID, ready to match against.
			$dbUser = $this->_api[$this]->getByUuid([
				"uuid" => $userHash
			]);
			if($dbUser->hasResult) {
				// There is a user in the database, but the cookies need
				// checking for authenticity before logging user in!
				$authHash_generated = hash("sha512",
					$userHash . $saltHash . APPSALT);
				if($authHash === $authHash_generated) {
					// Success - private salt data matches cookie - log in!
					return $this->userSession($dbUser);
				}
			}
			else {
				// There is no user of that UUID - delete any trace of cookies.
				$this->deleteCookies();
			}
		}
		else {
			// There are only some cookies stored - kill any trace of cookies.
			$this->deleteCookies();
		}
	}

	// At this point, there is no user logged in, so ensure there is no session
	// data about any users.
	if(!empty($_SESSION["PhpGt_User"])) {
		unset($_SESSION["PhpGt_User"]);
	}
	return false;
}

/**
 * Checks if there is any openId data in the querystring.
 */
private function checkOpenId() {
	if(isset($_GET["openid_identity"])) {
		$this->auth();
		if(isset($_GET["openid_return_to"])) {
			// Remove any potential ?Authenticate=OpenIdProvider from URI.
			$returnTo = preg_replace(
				"/(?<=[\?|&])Authenticate=\w+&?/i",
				"",
				$_GET["openid_return_to"]
			);

			header("Location: " . $returnTo);
			exit;
		}
	}
}

/**
 * Creates the User session for internal use by this tool. Can accept a UUID
 * as a string, an ID as an integer, or a DbResult object to extract values
 * from.
 * @param  int|string|DbResult $input The user data to store in the session.
 * @param  string              $anonUuid If a user has identified, the UUID of 
 * the anonymous user can be passed here, which will be set on the user session
 * object, allowing app developers to merge user accounts if required.
 * @return array        The user details.
 */
private function userSession($input, $anonUuid = null) {
	if(is_int($input)) {
		$dbUser = $this->_api[$this]->getById(["ID" => $input]);
	}
	else if(is_string($input)) {
		$dbUser = $this->_api[$this]->getByUuid(["uuid" => $input]);
	}
	else {
		$dbUser = $input;
	}

	if($dbUser->hasResult) {
		if(empty($_SESSION["PhpGt_User"])) {
			$_SESSION["PhpGt_User"] = array();
		}
		$_SESSION["PhpGt_User"]["ID"] = $dbUser["ID"];
		$_SESSION["PhpGt_User"]["uuid"] = $dbUser["uuid"];
		$_SESSION["PhpGt_User"]["username"] = $dbUser["username"];

		if (!is_null($anonUuid)) {
			$anonDb = $this->_api[$this]->getByUuid(["uuid" => $anonUuid]);
			if($anonDb->hasResult) {
				$_SESSION["PhpGt_User"]["orphanedID"] = $anonDb["ID"];
			}
		}

		return $_SESSION["PhpGt_User"];
	}

	return null;
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

	if(!$this->checkWhiteList($username) 
	|| empty($username)) {
		//$this->unAuth();
		throw new HttpError(403, 
			"The supplied account is not authorised for this application.");
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

	if(isset($_SESSION["PhpGt_User.tool_whiteList"])) {
		$_SESSION["PhpGt_User.tool_whiteList"] = array_merge(
			$_SESSION["PhpGt_User.tool_whiteList"], 
			$whiteListArray);
	}
	else {
		$_SESSION["PhpGt_User.tool_whiteList"] = $whiteListArray;
	}
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
	if(empty($_SESSION["PhpGt_User.tool_whiteList"])) {
		$this->addWhiteList("*");
	}

	$whiteList = $_SESSION["PhpGt_User.tool_whiteList"];
	$result = false;

	foreach ($whiteList as $w) {
		if (preg_match("/^\/.*\/[a-zA-Z]*$/", $w)) {
			// Whitelist is a RegEx (preg_match returns 0 on no match, but 
			// false on error - note !==).
			if(preg_match($w, $username) > 0) {
				$result = true;
			}
		}
		else if(is_string($w)) {
			if(fnmatch($w, $username)) {
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
	$expires = strtotime("+105 weeks");
	$hash = hash("sha512", $uuid . $userSalt . APPSALT);
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
		$hash,
		$expires,
		"/");

	$_COOKIE["PhpGt_Login"] = array();
	$_COOKIE["PhpGt_Login"][0] = $uuid;
	$_COOKIE["PhpGt_Login"][1] = $userSalt;
	$_COOKIE["PhpGt_Login"][2] = $hash;
}

/**
 * Every time there is user activity, refresh the cookies to keep them alive.
 */
private function refreshCookies() {
	$expires = strtotime("+105 weeks");
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
	// Rather than deleting the UUID cookie (which will take 2 requests), set
	// it to a new UUID, so that the user can start to be anonymously tracked
	// straight away.
	// Time expired is set to 1 second after EPOCH (otherwise, 0 sets the cookie
	// to a session expiration).
	setcookie("PhpGt_Track", "deleted", 1, "/");
	unset($_COOKIE["PhpGt_Track"]);
	
	setcookie("PhpGt_Login[0]", "deleted", 1, "/");
	setcookie("PhpGt_Login[1]", "deleted", 1, "/");
	setcookie("PhpGt_Login[2]", "deleted", 1, "/");
	unset($_COOKIE["PhpGt_Login"]);
}

/**
 * Increments the activity indicator in the user table, and sets the last
 * active dateTime to now().
 * @param int $id The ID of the user, or leave blank for the current user.
 */
private function setActive($id = null) {
	if(is_null($id)) {
		$id = $_SESSION["PhpGt_User"]["ID"];
	}
	$this->_api[$this]->setActive(["ID" => $id]);
}

/**
 * Creates a UUID for tracking anonymous users.
 * @return string The UUID.
 */
private function generateSalt() {
	return hash("sha512", uniqid(APPSALT, true));
}

}?>