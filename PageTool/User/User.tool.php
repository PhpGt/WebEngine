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
		return $_COOKIE["PhpGt_User_PageTool"];
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
	// The database will be checked for existing user matching OAuth data...
	// ... if there is no match, one will be stored.
	$dbUser = null;
	$providerList = $auth->providerList;
	$oAuthMissing = array();
	foreach ($providerList as $provider) {
		$profile = $auth->getProfile($provider);
		$uid = $profile->identifier;
		$oauth_uuid = $provider . $uid;

		$existingOAuthUser = $this->_api[$this]->getByOAuthUuid([
			"oauth_uuid" => $oauth_uuid,
		]);
		if($existingOAuthUser->hasResult) {
			$dbUser = $existingOAuthUser->result[0];
		}
		else {
			// Store the missing OAuth records once the user ID is found.
			$oAuthMissing[$provider] = $oauth_uuid;
		}
	}

	if(is_null($dbUser)) {
		// The user doesn't have any OAuth records yet, so we don't have a
		// reference to the user - get it from the tracking ID.
		$dbUser = $this->_api[$this]->getByUuid([
			"uuid" => $this->track(),
		]);
		if(!$dbUser->hasResult) {
			// Impossible situation - there's no user found from UUID.
			throw new HttpError(500, "User tracking code mismatch!");
		}
		// Mark the user as identified.
		$dbUser = array_merge($dbUser->result[0], [
			"dateTimeIdentified" => date("Y-m-d H:i:s"),
		]);
		$this->_api[$this]->anonIdentify([
			"username" => null,
			"uuid" => $this->track(),
		]);

		// Pull the user out of the database again now it has been updated.
		$dbUser = $this->_api[$this]->getByUuid([
			"uuid" => $this->track(),
		]);
		$dbUser = $dbUser->result[0];
	}

	// At this point $dbUser definitely refers to an existing user, but OAuth
	// records may still be missing... create them!
	foreach ($oAuthMissing as $provider => $oauth_uuid) {
		$this->_api[$this]->linkOAuth([
			"FK_User" => $dbUser["ID"],
			"oauth_uuid" => $oauth_uuid,
			"oauth_name" => $provider,
		]);
	}

	// Assign the user details to the session object, taking all dbUser fields
	// and adding extras.
	$_SESSION["PhpGt.User_PageTool"] = array_merge($dbUser, [
		"dateTimeLastActive" => date("Y-m-d H:i:s"),
		"isIdentified" => true,
		"providerList" => $providerList,
	]);

	return true;
}

/**
 * Checks for a tracking cookie, and if it doesn't exist, creates one.
 * @param  $force Optional. Pass in a new uuid to track with.
 * @return string The tracking UUID.
 */
public function track($forceUuid = null) {
	if(empty($_COOKIE["PhpGt_User_PageTool"]) || !is_null($forceUuid)) {
		$uuid = is_null($forceUuid)
			? $this->generateSalt()
			: $forceUuid;
		$expires = strtotime("+105 weeks");
		if(!setcookie("PhpGt_User_PageTool", $uuid, $expires, "/")) {
			throw new HttpError(500,
				"Error generating tracking cookie in User PageTool.");
		}
		$_COOKIE["PhpGt_User_PageTool"] = $uuid;
		return $uuid;
	}

	return $_COOKIE["PhpGt_User_PageTool"];
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
	die("Use of dead function: userSession");
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
 * Synonym for logout.
 */
public function unAuth($auth = null) {
	return $this->logout($auth);
}
/**
 * Removes the current authorisation cookie and also optionally deauthenticates
 * the Auth object.
 * @param  Auth $auth   Authentication object to disconnect all providers.
 */
public function logout($auth = null) {
	$_COOKIE["PhpGt_User_PageTool"] = null;
	setcookie("PhpGt_User_PageTool", 0, 1, "/");
	if(!is_null($auth)) {
		$auth->logout();
	}

	
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
	if(is_null($id)) {
		return false;
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

}#