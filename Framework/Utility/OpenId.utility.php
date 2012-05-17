<?php
/**
 * TODO: Docs.
 */
require_once(GTROOT . DS . "Framework" . DS . "Utility" 
				. DS . "OpenId" . DS . "OpenId.php");

final class OpenId_Utility {
	private $_openId = null;
	private $_attributes = null;
	private $_identities = array(
		"Google" => "https://www.google.com/accounts/o8/id"
	);

	/**
	 * TODO: Docs.
	 */
	public function __construct($identity = "Google") {
		$identityStr = $this->getIdentityString($identity);
		$domain = $_SERVER['HTTP_HOST'];

		try {
			$this->_openId = new LightOpenID($domain);
			$this->_openId->required = array("contact/email");

			if(!$this->_openId->mode) {
				$this->_openId->identity = $identityStr;
				header("Location: " . $this->_openId->authUrl());
				exit;
			}
			else if($this->_openId->mode === "cancel") {
				// TODO: Do something when the user cancels authentication.
				die("ERROR: User canceled auth!");
			}
			else {
				if($this->_openId->validate()) {
					$this->_attributes = $this->_openId->getAttributes();
				}
				else {
					// TODO: Do something on false validation.
					var_dump($this->_openId->getAttributes());
					var_dump($this->_openId);
					die("ERROR: OpenId validation failed...");
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
}
?>