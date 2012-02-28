<?php
/**
 * TODO: Docs (holds default settings, can be overridden by apps).
 */
class Database_Config_Framework {
	protected $_host = "127.0.0.1";
	protected $_name;
	protected $_user;
	protected $_pass;

	protected $_driver = "mysql";
	protected $_paramChar = ":";

	// The creation order of PHP.Gt tables (some may rely on others in foreign
	// key constraints, for example).
	private $_sharedCreationOrder = array(
		"User",
		"Content",
		"Blog"
	);

	// The creation order of application specific tables. These will always be
	// created *after* the PHP.Gt tables.
	protected $_creationOrder = array(
	);

	public function __construct() {
		$this->_name = isset($this->_name)
			? $this->_name
			: "Gt_" . APPNAME;
		$this->_user = isset($this->_user)
			? $this->_user
			: "Gt_" . APPNAME;
		$this->_pass = isset($this->_pass)
			? $this->_pass
			: "Gt_" . APPNAME . "_Pass";
	}

	public function getCreationOrder() {
		return array_merge(
			$this->_sharedCreationOrder,
			$this->_creationOrder);
	}

	public function getSettings() {
		return array(
			"ConnectionString" => 
				$this->_driver 
				. ":dbname=" . $this->_name 
				. ";host=" . $this->_host,
			"ConnectionString_Root" =>
				$this->_driver
				. ":host=" . $this->_host,
			"Username"	=> $this->_user,
			"Password"	=> $this->_pass,
			"ParamChar"	=> $this->_paramChar,
			"DbName" 	=> $this->_name,
			"Host"		=> $this->_host
		);
	}
}
?>