<?php class Auth {
/**
 * Auth provides a simple wrapper to the HybridAuth library. To use, pass an
 * array of provider names with id/secret pairs into the Auth constructor and 
 * call the login/logout functions when needed.
 *
 * When using one (or arbitary) login providers, user profile properties can be
 * accessed directly from the Auth object.
 *
 * IMPORTANT: The authentication tokens only last a few minutes. It's your
 * responsibility to record the authenticated details against the session or
 * User PageTool's uuid in order to offer persistent login.
 *
 * Example: 
 * $providers = array(
 *     "Google" => ["id" => "1234", "secret" => 5678],
 *     "Twitter" => ["id" => "1234", "secret" => 5678],
 * );
 * $auth = new Auth($providers);
 * if(isset($_GET["Login"])) {
 *     // Assume the provider is set in the Login querystring parameter.
 *     $auth->login($_GET["Login"]);
 * }
 *
 * // Check if authenticated:
 * if($auth->isAuthenticated) {
 *     // Do something with their data, like this:
 *     $message = "Hello, " . $auth->firstName;
 * }
 * 
 * http://hybridauth.sourceforge.net
 */

public $config = array(
	"base_url" => "",
					// Set to another URL if a specific page deals with auth
					// e.g. /Login.html so Login_PageCode performs auth.

	"providers" => array( 
		// openid providers
		"OpenID" => array(
			"enabled" => true
		),

		"Yahoo" => array( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ),
		),
		"AOL"  => array( 
			"enabled" => true 
		),
		"Google" => array( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ), 
		),
		"Facebook" => array( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ), 
		),
		"Twitter" => array( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),
		"Live" => array( // windows live
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ) 
		),
		"MySpace" => array( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),
		"LinkedIn" => array( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),
		"Foursquare" => array(
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ) 
		),
	),

	// if you want to enable logging, set 'debug_mode' to true then provide a 
	// writable file by the web server on "debug_file"
	"debug_mode" => false,
	"debug_file" => "",
);

private $hybridAuth = null;
private $adapter = null;
private $profile = null;

public function __construct($providerConfig = array()) {
	// require_once(__DIR__ . "/hybridauth/Hybrid/Auth.php");
	// require_once(__DIR__ . "/hybridauth/Hybrid/Endpoint.php");

	// if($this->checkCache()) {
	// 	$this->useCache();
	// 	return;
	// }

	if(empty($this->config["base_url"])) {
		$this->config["base_url"] = 
			"http" . (empty($_SERVER["HTTPS"]) ? "" : "s")
			. "://"
			. $_SERVER["SERVER_NAME"]
			. "/";
	}

	foreach ($providerConfig as $pName => $pDetails) {
		if(isset($pDetails["ID"])) {
			// Lower-case the ID key if supplied in upper-case.
			$pDetails["id"] = $pDetails["ID"];
			unset($pDetails["ID"]);
		}
		
		// Remove the id/key ambiguity.
		if(isset($pDetails["id"])) {
			if(!isset($this->config["providers"][$pName]["keys"]["id"])
			&&  isset($this->config["providers"][$pName]["keys"]["key"])) {
				$pDetails["key"] = $pDetails["id"];
				unset($pDetails["id"]);
			}
		}
		if(isset($pDetails["key"])) {
			if(!isset($this->config["providers"][$pName]["keys"]["key"])
			&&  isset($this->config["providers"][$pName]["keys"]["id"])) {
				$pDetails["id"] = $pDetails["key"];
				unset($pDetails["key"]);
			}
		}

		$this->config["providers"][$pName] = array(
			"enabled" => true,
			"keys" => $pDetails,
		);

		if(isset($pDetails["scope"])) {
			$this->config["providers"][$pName]["scope"] = $pDetails["scope"];
		}

		if(isset($_GET["hauth_start"])
		|| isset($_GET["hauth_done"])) {
			Hybrid_Endpoint::process();
		}
	}
	$this->hybridAuth = new Hybrid_Auth($this->config);
	// $this->createCache();
	return;
}

// private function createCache() {
// 	if(isset($_SESSION["PhpGt_Cache"])) {
// 		$_SESSION["PhpGt_Cache"]["Auth"] = array();
// 	}
// 	else {
// 		$_SESSION["PhpGt_Cache"] = array("Auth" => array());
// 	}
// 	$_SESSION["PhpGt_Cache"]["Auth"]["Hybrid_Auth"] = $this->hybridAuth;
// 	return;
// }

// /**
//  * Checks all session variables used in the Auth cache. If any variable is
//  * missing, the method will return false.
//  */
// private function checkCache() {
// 	return isset($_SESSION["PhpGt_Cache"])
// 		&& isset($_SESSION["PhpGt_Cache"]["Auth"])
// 		&& !empty($_SESSION["PhpGt_Cache"]["Auth"]["Hybrid_Auth"])
// 		&& !empty($_SESSION["PhpGt_Cache"]["Auth"]["Adapter"])
// 		&& !empty($_SESSION["PhpGt_Cache"]["Auth"]["Profile"]);
// }

// /**
//  * Allocates the three member variables with whatever is stored within the
//  * session cache.
//  */
// private function useCache() {
// 	$this->hybridAuth = $_SESSION["PhpGt_Cache"]["Auth"]["Hybrid_Auth"];
// 	$this->adapter = $_SESSION["PhpGt_Cache"]["Auth"]["Adapter"];
// 	$this->profile = $_SESSION["PhpGt_Cache"]["Auth"]["Profile"];
// 	return;
// }

// private function setCache() {
// 	$_SESSION["PhpGt_Cache"]["Auth"]["Hybrid_Auth"] = $this->hybridAuth;
// 	$_SESSION["PhpGt_Cache"]["Auth"]["Adapter"] = $this->adapter;
// 	$_SESSION["PhpGt_Cache"]["Auth"]["Profile"] = $this->profile;
// }

/**
 * Synonym for `authenticate` method.
 */
public function login($provider) {
	return $this->authenticate($provider);
}

public function authenticate($provider) {
	// if($this->checkCache()) {
	// 	$this->useCache();
	// 	return;
	// }

	try {
		$this->adapter = $this->hybridAuth->authenticate($provider);
		$this->profile = $this->adapter->getUserProfile();

		// $this->setCache();

		// $profile contains "identifier" property, the UUID to the user, 
		// used for storing in the app database.
		// Also may contain these properties for identification:
		// email, firstName, lastName, displayNAme, webSiteURL, profileURL.
	}
	catch(Exception $e) {
		switch($e->getCode()) {
		case 0:
			$error = "Unspecified error.";
			break;
		case 1:
			$error = "Hybriauth configuration error.";
			break;
		case 2:
			$error = "Provider not properly configured.";
			break;
		case 3:
			$error = "Unknown or disabled provider.";
			break;
		case 4:
			$error = "Missing provider application credentials.";
			break;
		case 5:
			$error = "Authentication failed. The user has canceled the "
				. "authentication or the provider refused the connection.";
			break;
		case 6: 
			$error = "User profile request failed. Most likely the user is "
				. "not connected to the provider and he should to "
				. "authenticate again.";
			$this->adapter->logout();
			break;
		case 7:
			$error = "User not connected to the provider."; 
			$this->adapter->logout(); 
			break;
		}

		throw new HttpError(500, "HybridAuth error: $error");
	}
}

public function logout() {
	$this->hybridAuth->logoutAllProviders();
	// $this->setCache();
	return;
}

/**
 * Synonym for getConnectedProfile.
 */
public function getProfile($provider) {
	if(is_null($this->adapter)) {
		$this->login($provider);
	}
	
	return $this->getConnectedProfile($provider);
}
/**
 * Synonym for getConnectedProfile.
 */
public function profile($provider) {
	return $this->getConnectedProfile($provider);
}
/**
 * Returns the profile for the given provider identifier.
 * @param  string $provider    Name of the provider.
 * @return Hybrid_User_Profile Profile for requested provider, or null if the
 * profile is not connected.
 */
public function getConnectedProfile($provider) {
	// if($this->checkCache()) {
	// 	$this->useCache();
	// 	return $this->profile;
	// }

	try {
		$adapter = $this->hybridAuth->authenticate($provider);
		$profile = $adapter->getUserProfile();

		// $this->setCache();

		return $profile;
	}
	catch(Exception $e) {
		return null;
	}
}

public function getConnectedProviders() {
	// if($this->checkCache()) {
	// 	$this->useCache();
	// }

	return $this->hybridAuth->getConnectedProviders();
}

public function isConnectedWith($provider) {
	// if($this->checkCache()) {
	// 	$this->useCache();
	// }

	return $this->hybridAuth->isConnectedWith($provider);
}

/**
 * Allows some wrapper properties to be used for quick data access, but mainly
 * provides shorthand properties to authenticated data. The developer doesn't 
 * need to know which provider is used to be able to obtain the user's name, 
 * for example.
 */
public function __get($name) {
	switch($name) {
	case "accountList":
	case "accounts":
	case "authenticated":
	case "connected":
	case "connectedAccounts":
	case "connectedProviders":
	case "loggedIn":
	case "providerList":
	case "providers":
		return $this->getConnectedProviders();
		break;
	case "isAuthenticated":
	case "isConnected":
	case "isLoggedIn":
		$connectedProviders = $this->getConnectedProviders();
		return !empty($connectedProviders);
		break;
	default:
		$connectedProviders = $this->getConnectedProviders();
		foreach ($connectedProviders as $provider) {
			try {
				$adapter = $this->hybridAuth->authenticate($provider);
				$profile = $adapter->getUserProfile();
				if(isset($profile->$name)) {
					return $profile->$name;
				}
			}
			catch(Exception $e) {
				continue;
			}
		}

		return null;
		break;
	}
}


}#