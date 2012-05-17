<?php
class User_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {
		if(empty($_COOKIE["PhpGt_Track"])) {
			$anonId = $this->generateSalt();
			$expires = strtotime("+2 weeks");
			if(setcookie("PhpGt_Track", $anonId, $expires) === false) {
				// TODO: Throw proper error. Cookie can't be set if
				// any output has been made!
				die("Can't set PhpGt_Track cookie!");
			}
			return $anonId;
		}

		return $_COOKIE["PhpGt_Track"];
	}

	public function get() {
		return $_SESSION["PhpGt_User"];
	}

	public function auth($method = "Google") {
		$oid = new OpenId_Utility($method);
		$username = $oid->getData();
		if(is_null($username)) {
			$this->unAuth();
			return false;
		}

		$_SESSION["PhpGt_User.tool_AuthData"] = $username;

		$uuid = hash("sha512", $username);
		$userSalt = $this->generateSalt();
		$expires = strtotime("+2 weeks");
		setcookie(
			"PhpGt_Login[0]",
			$uuid,
			$expires);
		setcookie(
			"PhpGt_Login[1]",
			$userSalt,
			$expires);
		setcookie(
			"PhpGt_Login[2]",
			hash("sha512", $uuid . $userSalt . APPSALT),
			$expires);
		return true;
	}

	public function unAuth() {
		unset($_SESSION["PhpGt_User.tool_AuthData"]);
		unset($_SESSION["PhpGt_User"]);
		$this->deleteCookies();
	}

	/**
	 * Checks the current session for authentication data. This may be
	 * authentication with OpenId or using a simple username/password stored
	 * in the User database table.
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
			if($dbUser->hasResult) {
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
								// Something's gone wrong... there is proper
								// cookie authentication, but no user exists
								// in database!
								// TODO: Log this error.
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

	private function refreshCookies() {
		$expires = strtotime("+2 weeks");
		setcookie(
			"PhpGt_Login[0]",
			$_COOKIE["PhpGt_Login"][0],
			$expires);
		setcookie(
			"PhpGt_Login[1]",
			$_COOKIE["PhpGt_Login"][1],
			$expires);
		setcookie(
			"PhpGt_Login[2]",
			$_COOKIE["PhpGt_Login"][2],
			$expires);
	}

	private function deleteCookies() {
		unset($_COOKIE["PhpGt_Login"]);
		setcookie("PhpGt_Login[0]", "deleted", 0);
		setcookie("PhpGt_Login[1]", "deleted", 0);
		setcookie("PhpGt_Login[2]", "deleted", 0);
	}

	private function generateSalt() {
		// TODO: A real salt function.
		return hash("sha512", rand(0, 10000));
	}
}
?>