<?php
class App_Config_Framework {
	protected $_activeModules;
	protected $_isCached = true;
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
}
?>