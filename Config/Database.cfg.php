<?php
class Database_Config_Framework {
	protected $_host = "127.0.0.1";
	protected $_name;
	protected $_user;
	protected $_pass;

	protected $_driver = "mysql";
	protected $_paramChar = ":";

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

	public function getSettings() {
		return array(
			"ConnectionString" => 
				$this->_driver 
				. ":dbname=" . $this->_name 
				. ";host=" . $this->_host,
			"Username"	=> $this->_user,
			"Password"	=> $this->_pass,
			"ParamChar"	=> $this->_paramChar,
			"DbName" 	=> $this->_name,
			"Host"		=> $this->_host
		);
	}
}
?>