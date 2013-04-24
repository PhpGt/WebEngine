<?php class Auth {
/**
 * Auth provides a simple wrapper to the HybridAuth library. To use, pass an
 * array of provider names with id/secret pairs into the Auth constructor and 
 * call the login/logout functions when needed.
 *
 * When using one (or arbitary) login providers, user profile properties can be
 * accessed directly from the Auth object.
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
	"base_url" => "http://test.dev.php.gt",
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

		// windows live
		"Live" => array( 
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
	require_once(__DIR__ . "/hybridauth/Hybrid/Auth.php");
	require_once(__DIR__ . "/hybridauth/Hybrid/Endpoint.php");

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

		if(isset($_GET["hauth_start"])
		|| isset($_GET["hauth_done"])) {
			Hybrid_Endpoint::process();
		}
	}
	
	$this->hybridAuth = new Hybrid_Auth($this->config);
}

/**
 * Synonym for `authenticate` method.
 */
public function login($provider) {
	return $this->authenticate($provider);
}

public function authenticate($provider) {
	try {
		$this->adapter = $this->hybridAuth->authenticate($provider);
		$this->profile = $this->adapter->getUserProfile();

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
			$adapter->logout();
			break;
		case 7:
			$error = "User not connected to the provider."; 
			$adapter->logout(); 
			break;
		}

		// TODO: Throw proper PHP.Gt error once tested.
		die("HybridAuth error: $error");
	}
}

public function logout() {
	return $this->hybridAuth->logoutAllProviders();
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
		$adapter = $this->hybridAuth->authenticate($provider);
		$profile = $adapter->getUserProfile();
		return $profile;
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


}?>