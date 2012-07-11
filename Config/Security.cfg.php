<?php class Security_Config_Framework {
protected $_remoteSetupWhitelist = array(
	"127.0.0.1"
);
protected $_allowAllRemoteAdmin = true;
protected $_remoteAdminWhiteList = array(
	"127.0.0.1"
);
protected $_salt = "Php.Gt default salt - please change this!";
protected $_domain;
private $_remoteIp;

public function __construct() {
	$this->_remoteIp = $_SERVER["REMOTE_ADDR"];
	$this->_domain = isset($this->_domain)
		? $this->_domain
		: $_SERVER["HTTP_HOST"];
	define("APPSALT", $this->_salt);
}

public function getDomain() {
	return $this->_domain;
}

public function isSetupAllowed() {
	return in_array($this->_remoteIp, $this->_remoteSetupWhitelist);
}

public function isAdminAllowed() {
	return in_array($this->_remoteIp, $this->_remoteAdminWhiteList)
		|| $this->_allowAllRemoteAdmin;
}

}?>