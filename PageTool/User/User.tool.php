<?php class User_PageTool extends PageTool {
/**
 * User PageTool is used to provide authorisation within your application, 
 * along with anonymous users for applications that don't require signing up but
 * do require persistent storage. Anonymous users can then be converted into
 * full users by regular authorisation.
 *
 * If authorisation is not required in your application, 
 * getUser() is called to get reference to a database user, authorised or
 * simply anonymous. This is a less-strict version of checkAuth().
 */

private $_whiteList = array();

public function go($api, $dom, $template, $tool) {}

public function __get($name) {
	if(empty($_SESSION["PhpGt.User_PageTool"])) {
		$user = $this->getUser();
	}
	else {
		$user = $_SESSION["PhpGt.User_PageTool"];
	}

	switch($name) {
	case "authenticated":
	case "isAuthenticated":
	case "username":
	case "userName":
		die("User PageTool does not 'authenticate', "
			. "it only provides authorisation.");
		break;
	case "id":
		return $user["ID"];
		break;
	case "uuid":
		return empty($_COOKIE["PhpGt_Login"])
			? $_COOKIE["PhpGt.User_PageTool.Track"]
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
 * Used like checkAuth(), but doesn't require authorisation. GetUser can be 
 * used to allow anonymous users to use the application, and be treated as
 * usual users in terms of database storage and returned values.
 * If the UUID doesn't exist in the database, a new anonymous user will be
 * created.
 * @param Auth $auth    An instance of Auth object, representing an OAuth user.
 * @return array        The user details, identified or anonymous.
 */
public function getUser($auth = null) {
	// Ensure there is a UUID tracking cookie set.
	$uuid = $this->track();

	// If user is authorised already, return early with the authorised
	// user detains in an array.
	if($this->checkAuth($auth)) {
		$this->setActive();
		return $_SESSION["PhpGt.User_PageTool"];
	}
	
	// Ensure there is a related user in the database.
	// If a user doesn't exist, create one.
	$db = $this->_api[$this];
	$dbUser = $db->getByUuid(["uuid" => $uuid]);

	if($dbUser->hasResult) {
		$isIdentified = $dbUser["User_Type__name"] !== "Anon";
		$dbUser->setData("isIdentified", $isIdentified);
		$dbUser = $dbUser->result[0];
		$dbUser["isIdentified"] = false;
	}
	else {
		$result = $db->addAnon(["uuid" => $uuid]);
		// Build an array that matches what is stored in the database.
		$dbUser = array(
			"ID" => $result->lastInsertId,
			"uuid" => $uuid,
			"username" => null,
			"dateTimeIdentified" => null,
			"dateTimeLastActive" => date("Y-m-d H:i:s"),
			"User_Type__name" => "Anon",
			"isIdentified" => false,
		);
	}

	$this->setActive($dbUser["ID"]);

	// Return the array, or array-like-object representing the user.
	return $dbUser;
}

/**
 * Checks the given Auth object for authentication. If there is no 
 * authentication, the method will simply return false. If there is 
 * authentication, the authenticated details will be mapped to the user's
 * database record which in turn will be stored in the PhpGt.User_PageTool
 * session variable, before returning true.
 *
 * @param Auth $auth 	An instance of Auth object, representing an OAuth user.
 * @return bool			True if $auth is authenticated, false if not.
 */
public function checkAuth($auth) {
	if(!$auth->isAuthenticated) {
		// NOTE: It was planned to allow standard username/password storage at
		// this position, but a better idea for apps that require their own
		// authorisation is to use a local OAuth server.
		return false;
	}

	// The user is authenticated to at least one OAuth provider.
	// The database will be ckecked for existing user matching OAuth data...
	// ... if there is no match, one will be stored.
	$providerList = $auth->providerList;
	foreach ($providerList as $provider) {
		$profile = $auth->getProfile($provider);
		$uid = $profile->identifier;
		$oauth_uuid = $provider . $uid;

		$existingOAuthUser = $this->_api[$this]->getByOauthUuid([
			"oauth_uuid" => $oauth_uuid
		]);
		if($existingOAuthUser->hasResult) {
			$_SESSION["PhpGt.User_PageTool"] = array(
				"ID"                => $existingOAuthUser["ID"],
				"uuid"              => $existingOAuthUser["uuid"],
				"username"          => $existingOAuthUser["username"],
				"dateTimeIdentified"=> $existingOAuthUser["dateTimeIdentified"],
				"dateTimeLastActive"=> date("Y-m-d H:i:s"),
				"User_Type__name"   => $existingOAuthUser["User_Type__name"],
				"isIdentified"      => true,
			);
			return true;
		}
	}

	// At this point, no OAuth data is found in the database. Create it, store
	// it to the session and return true.
	var_dump($auth);die("PIGS");
	return true;

	// The PhpGt.User_PageTool.tool_AuthData session key is used for OAuth, cookie or 
	// other login mechanisms to store an authenticated username, for full
	// authentication in this function.
	if(isset($_SESSION["PhpGt.User_PageTool.tool_AuthData"])) {
		$oauth_uuid = $_SESSION["PhpGt.User_PageTool.tool_OAuth_uuid"];

		// User has authenticated in some way, and a username is known.
		// Need to match to a database user, which may not exist yet.
		$dbUser = $this->_api[$this]->get(["username" => $username]);
		if($dbUser->hasResult) {
			// User already stored in database.
			return $this->userSession($dbUser, $_COOKIE["PhpGt.User_PageTool.Track"]);
		}
		else {
			// Authenticated user doesn't exist in database, but there may be
			// an anonymous user in the database with same UUID.
			$anonDbUser = $this->_api[$this]->getByUuid(
				["uuid" => $_COOKIE["PhpGt.User_PageTool.Track"]]);
			if($anonDbUser->hasResult) {
				// Upgrade the anon user to full user.
				$oldUuid = $_COOKIE["PhpGt.User_PageTool.Track"];
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
					? $_COOKIE["PhpGt.User_PageTool.Track"]
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
	if(!empty($_SESSION["PhpGt.User_PageTool"])) {
		unset($_SESSION["PhpGt.User_PageTool"]);
	}
	return false;
}

/**
 * Checks for a tracking cookie, and if it doesn't exist, creates one.
 * @param  $force Optional. Pass in a new uuid to track with.
 * @return string The tracking UUID.
 */
public function track($forceUuid = null) {
	if(empty($_COOKIE["PhpGt_User_PageTool_Track"]) || !is_null($forceUuid)) {
		$uuid = is_null($forceUuid)
			? $this->generateSalt()
			: $forceUuid;
		$expires = strtotime("+105 weeks");
		if(!setcookie("PhpGt.User_PageTool.Track", $uuid, $expires, "/")) {
			throw new HttpError(500,
				"Error generating tracking cookie in User PageTool.");
		}
		$_COOKIE["PhpGt.User_PageTool.Track"] = $uuid;
		return $uuid;
	}

	return $_COOKIE["PhpGt_User_PageTool_Track"];
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
		if(empty($_SESSION["PhpGt.User_PageTool"])) {
			$_SESSION["PhpGt.User_PageTool"] = array();
		}
		$_SESSION["PhpGt.User_PageTool"]["ID"] = $dbUser["ID"];
		$_SESSION["PhpGt.User_PageTool"]["uuid"] = $dbUser["uuid"];
		$_SESSION["PhpGt.User_PageTool"]["username"] = $dbUser["username"];

		if (!is_null($anonUuid)) {
			$anonDb = $this->_api[$this]->getByUuid(["uuid" => $anonUuid]);
			if($anonDb->hasResult) {
				$_SESSION["PhpGt.User_PageTool"]["orphanedID"] = $anonDb["ID"];
				$this->mergeOrphan();
			}
		}

		return $_SESSION["PhpGt.User_PageTool"];
	}

	return null;
}

/**
 * Begins the authentication process using the given provider. Valid providers
 * are OAuth providers including: "Google", "Facebook", "MyOpenId".
 * @param  string $method The authentication provider.
 * @return bool           True if the user successfully authenticates.
 */
public function auth($method = "Google", $forwardTo = null) {
	if(is_null($forwardTo)) {
		if(empty($_SESSION["PhpGt.User_PageTool.tool_ForwardTo"])) {
			if(empty($_SERVER["HTTP_REFERER"])) {
				$forwardTo = "/";
			}
			else {
				$forwardTo = $_SERVER["HTTP_REFERER"];
			}
			
			$_SESSION["PhpGt.User_PageTool.tool_ForwardTo"] = $forwardTo;
		}
	}

	$oid = new OpenId($method);
	$username = $oid->getData();

	if(!$this->checkWhiteList($username) 
	|| empty($username)) {
		//$this->unAuth();
		var_dump($_SESSION);die();
		throw new HttpError(403, 
			"The supplied account is not authorised for this application.");
		return false;
	}
	$this->setAuthData($username);

	$this->authComplete();

	return true;
}

/**
 * [authComplete description]
 * @return [type] [description]
 */
private function authComplete() {
	if(!empty($_SESSION["PhpGt.User_PageTool.tool_ForwardTo"])) {
		$forwardTo = $_SESSION["PhpGt.User_PageTool.tool_ForwardTo"];
		unset($_SESSION["PhpGt.User_PageTool.tool_ForwardTo"]);
		header("Location: $forwardTo");
		exit;
	};

	return;
}

/**
 * Unauthenticates any logged in user and removes any cookies set.
 * @param  string $forwardTo Where to forward the user after unauthenticating.
 */
public function unAuth($forwardTo = "/") {
	unset($_SESSION["PhpGt.User_PageTool.tool_AuthData"]);
	unset($_SESSION["PhpGt.User_PageTool"]);
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

	// The session persists... this should be stored as a private.
	if(!empty($this->_whiteList)) {
		$this->_whiteList = array_merge($this->_whiteList, $whiteListArray);
		$this->_whiteList = array_unique($this->_whiteList);
	}
	else {
		$this->_whiteList = $whiteListArray;
	}

	$_SESSION["PhpGt.User_PageTool.tool_AuthWhiteList"] = $this->_whiteList;
}

/**
 * Checks to see if the given username is allowed to authenticate to the 
 * application according to the optional whitelist.
 * @param  string $username Full username (email)
 * @return bool             True if the given username fits the optional 
 * whitelist parameters.
 */
public function checkWhiteList($username) {
	return true;
	// If there is no whitelist, allow all.
	if(empty($_SESSION["PhpGt.User_PageTool.tool_AuthWhiteList"])) {
		$this->addWhiteList("*");
	}

	$whiteList = $this->_whiteList;
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
	$_SESSION["PhpGt.User_PageTool.tool_AuthData"] = $username;

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
	setcookie("PhpGt.User_PageTool.Track", "deleted", 1, "/");
	unset($_COOKIE["PhpGt.User_PageTool.Track"]);
	
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
		$id = $_SESSION["PhpGt.User_PageTool"]["ID"];
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

/**
 * Merges two records in the User table. The record for the current user in 
 * session is kept, and the orphaned user record is dropped.
 * @return  bool True on successful merge, false if there is no orphan record.
 */
public function mergeOrphan() {
	$user = $_SESSION["PhpGt.User_PageTool"];
	if(empty($user["orphanedID"])) {
		return false;
	}

	$dbResult = $this->_api[$this]->mergeOrphan($user);

	return $dbResult->affectedRows > 0;
}

}?>