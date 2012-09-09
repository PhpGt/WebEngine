<?php class Database_Config_Framework {
/**
 * All details of the database connection are stored in this file. 
 * By default, there are certain connection settings that need to be changed 
 * per-application, such as the database username and password, and possibly IP 
 * address if an external server is used.
 *
 * The order of automatic deployment of database tables is specified here, so 
 * any table dependencies can be specified.
 */
protected $_host = "127.0.0.1";
protected $_port = "3306";
protected $_charset = "utf8";
protected $_name;
protected $_user;
protected $_pass;

protected $_driver = "mysql";

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
			. ":dbname=" 	. $this->_name 
			. ";host=" 		. $this->_host
			. ";port=" 		. $this->_port
			. ";charset=" 	. $this->_charset,
		"ConnectionString_Root" =>
			$this->_driver
			. ":host=" . $this->_host,
		"Username"	=> $this->_user,
		"Password"	=> $this->_pass,
		"DbName" 	=> $this->_name,
		"Host"		=> $this->_host
	);
}

}?>