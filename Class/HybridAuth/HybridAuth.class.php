<?php class HybridAuth {

private $_config = array(
	"base_url" => "http://localhost/hybridauth-git/hybridauth/", 

	"providers" => array ( 
		// openid providers
		"OpenID" => array (
			"enabled" => true
		),

		"Yahoo" => array ( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ),
		),

		"AOL"  => array ( 
			"enabled" => true 
		),

		"Google" => array ( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ), 
		),

		"Facebook" => array ( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ), 
		),

		"Twitter" => array ( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),

		// windows live
		"Live" => array ( 
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ) 
		),

		"MySpace" => array ( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),

		"LinkedIn" => array ( 
			"enabled" => true,
			"keys"    => array ( "key" => "", "secret" => "" ) 
		),

		"Foursquare" => array (
			"enabled" => true,
			"keys"    => array ( "id" => "", "secret" => "" ) 
		),
	),

	// if you want to enable logging, set 'debug_mode' to true then provide a writable file by the web server on "debug_file"
	"debug_mode" => false,

	"debug_file" => "",
);

private $_hybridAuth = null;

public function __construct($provider) {
	require_once(__DIR__ . "/hybridauth/Hybrid/Auth.php");
	try {
		$this->hybridAuth = new Hybrid_Auth($this->_config);
		$this->adapter = $this->_hybridAuth->authenticate($provider);
		$this->profile = $adapter->getUserProfile();

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


}?>