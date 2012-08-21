<?php class App_Config_Framework {
/**
 * All config classes suffixed with _Framework hold configuration settings
 * that apply to all applications. All protected properties can be overridden
 * by application-specific versions of the class. App-specific versions are
 * stored in the Config application directory, and the classes are named
 * similarly, but drop the _Framework suffix.
*/
// When an application is set to production mode, errors will be less
// verbose and less debugging information is available.
protected $_isProduction = false;
protected $_isCached = true;
protected $_isClientCompiled = false;

// When true, URLs are converted into directory style, dropping the need
// for the file extension.
protected $_directoryUrls = false;
protected $_timezone = "Europe/London";
private $_reserved = array("PhpGt", "Gt", "g105b", "admin");

public function __construct() { }

public function isCached() {
	return $this->_isCached;
}

public function getReserved() {
	return $this->_reserved;
}

public function getTimezone() {
	return $this->_timezone;
}

public function isClientCompiled() {
	return $this->_isClientCompiled;
}

public function isProduction() {
	return $this->_isProduction;
}

}?>