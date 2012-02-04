<?php
/**
 * All config classes suffixed with _Framework hold configuration settings
 * that apply to all applications. All protected properties can be overridden
 * by application-specific versions of the class. App-specific versions are
 * stored in the Config application directory, and the classes are named
 * similarly, but drop the _Framework suffix.
*/
class App_Config_Framework {
	protected $_activeModules;
	protected $_isCached = true;
	protected $_isClientCompiled = false;
	private $_reserved = array("Gt", "g105b", "admin");

	public function __construct() { }

	public function getActiveModules() {
		return $this->_activeModules;
	}

	public function isCached() {
		return $this->_isCached;
	}

	public function getReserved() {
		return $this->_reserved;
	}

	public function isClientCompiled() {
		return $this->_isClientCompiled;
	}
}
?>