<?php class OpenId {
/**
 * 
 */
private $_openId = null;
private $_attributes = null;
private $_defaultIdentity = "Google";
private $_identities = array(
	"Google" => "https://www.google.com/accounts/o8/id"
);

/**
 * TODO: Docs.
 */
public function __construct($identity = "") {
	require_once(__DIR__ . "/OpenId/openid.php");

	if(empty($identity)) {
		$identity = $this->_defaultIdentity;
	}

	$identityStr = $this->getIdentityString($identity);
	$domain = $_SERVER['HTTP_HOST'];

	try {
		$this->_openId = new LightOpenID($domain);
		$this->_openId->required = array("contact/email");
		$mode = $this->_openId->mode;

		if(!$mode) {
			$this->_openId->identity = $identityStr;
			header("Location: " . $this->_openId->authUrl());
			exit;
		}
		else if($mode === "cancel") {
			throw new HttpError(403, "User cancelled OpenId authentication");
			exit;
		}
		else {
			if($this->_openId->validate()) {
				$this->_attributes = $this->_openId->getAttributes();
			}
			else {
				throw new HttpError(403, "OpenId validation failed");
				exit;
			}
		}
	}
	catch(ErrorException $e) {
		die("Error Exception: " . $e->getMessage()
			. " Please check your internet connection.");
	}
}

/**
 * TODO: Docs.
 */
private function getIdentityString($identity) {
	if(array_key_exists($identity, $this->_identities)) {
		return $this->_identities[$identity];
	}
	return false;
}

/**
 * TODO: Docs.
 */
public function getData($attr = "contact/email") {
	if(!empty($this->_attributes)) {
		if(isset($this->_attributes[$attr])) {
			return $this->_attributes[$attr];
		}
		else {
			return false;
		}
	}
	else {
		return null;
	}
}

}?>