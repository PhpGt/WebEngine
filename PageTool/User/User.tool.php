<?php
class User_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {}

	/**
	 * Checks the current session for authentication data. This may be
	 * authentication with OpenId or using a simple username/password stored
	 * in the User database table.
	 */
	public function checkAuth() {
		$userId = null;
		$username = null;
		
		if(isset($_GET["openid_identity"])) {
			$this->auth();
			if(isset($_GET["openid_return_to"])) {
				header("Location: " . $_GET["openid_return_to"]);
				exit;
			}
		}

		if(isset($_SESSION["PhpGt_User"])) {
			if(empty($_SESSION["PhpGt_User"])) {
				unset($_SESSION["PhpGt_User"]);
				return false;
			}
		}
		
		if(isset($_SESSION["PhpGt_OpenId_Data"])) {
			$username = $_SESSION["PhpGt_OpenId_Data"];
			// User has just authenticated, needs to match to database user.
			// The database user may need creating.
			$dbUser = $this->_api["User"]->get(array(
				"Username" => $username
			));
			if($dbUser->hasResult) {
				// Already exists - use this user.
				$userId = $dbUser["Id"];
			}
			else {
				// Doesn't already exist - create and use new user.
				$newDbUser = $this->_api["User"]->addEmpty(array(
					"Username" => $username
				));
				$userId = $newDbUser->lastInsertId;
			}
		}

		if(!is_null($userId)) {
			$_SESSION["PhpGt_User"] = array(
				"Id" => $userId,
				"Username" => $_SESSION["PhpGt_OpenId_Data"]
			);
		}

		if(empty($_SESSION["PhpGt_User"])) {
			return false;
		}
		return $_SESSION["PhpGt_User"];
	}

	public function auth($method = "Google") {
		$oid = new OpenId_Utility($method);
		$userData = $oid->getData();
		$_SESSION["PhpGt_OpenId_Data"] = $userData;
	}

	public function unauth() {
		unset($_SESSION["PhpGt_OpenId_Data"]);
		unset($_SESSION["PhpGt_User"]);
	}
}
?>