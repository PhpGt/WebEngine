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

private $_data = null;

private $hybridAuth = null;
private $adapter = null;
private $profile = null;

public function __construct($providerConfig = array()) {
	if(empty($this->config["base_url"])) {
		$this->config["base_url"] = 
			"http" . (empty($_SERVER["HTTPS"]) ? "" : "s")
			. "://"
			. $_SERVER["SERVER_NAME"]
			. "/";
	}

	if(!isset($_SESSION["PhpGt_Auth"])) {
		$_SESSION["PhpGt_Auth"] = array();		
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
	return;
}

/**
 * Allows setting of arbritary data, internally accessible by providers that
 * require extra parameters or do not strictly follow OAuth2 spec.
 */
public function setData($data) {
	$this->_data = $data;
}

/**
 * Synonym for `authenticate` method.
 */
public function login($provider) {
	return $this->authenticate($provider);
}

public function authenticate($provider) {
	try {
		$this->adapter = $this->getAdapter($provider);
		$this->profile = $this->adapter->getUserProfile();

		return $this->hybridAuth->getSessionData();
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
	unset($_SESSION["PhpGt_Auth"]);
	$this->hybridAuth->logoutAllProviders();
	return;
}

public function getAdapter($provider) {
	$adapter = $this->hybridAuth->authenticate($provider, $this->_data);
	return $adapter;
}

/**
 * Synonym for getConnectedProfile.
 */
public function getProfile($provider) {
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
	try {
		$adapter = $this->getAdapter($provider);
		if(!isset($_SESSION["PhpGt_Auth"]["Profile"])) {
			$_SESSION["PhpGt_Auth"]["Profile"] = array();
		}
		if(empty($_SESSION["PhpGt_Auth"]["Profile"][$provider])) {
			$_SESSION["PhpGt_Auth"]["Profile"][$provider] = 
				$adapter->getUserProfile();
		}

		return $_SESSION["PhpGt_Auth"]["Profile"][$provider];
	}
	catch(Exception $e) {
		return null;
	}
}

public function getConnectedProviders() {
	return $this->hybridAuth->getConnectedProviders();
}

public function isConnectedWith($provider) {
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
	case "isIdentified":
		$connectedProviders = $this->getConnectedProviders();
		return !empty($connectedProviders);
		break;
	default:
		$connectedProviders = $this->getConnectedProviders();
		foreach ($connectedProviders as $provider) {
			try {
				$adapter = $this->getAdapter($provider);
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