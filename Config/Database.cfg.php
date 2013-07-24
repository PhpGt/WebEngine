<?php class Database_Config_Framework extends Config {
/**
 * All details of the database connection are stored in this file. 
 * By default, there are certain connection settings that need to be changed 
 * per-application, such as the database username and password, and possibly IP 
 * address if an external server is used.
 *
 * The order of automatic deployment of database tables is specified here, so 
 * any table dependencies can be specified.
 */
protected static $_host = "127.0.0.1";
protected static $_port = "3306";
protected static $_charset = "utf8";
protected static $_name;
protected static $_user;
protected static $_pass;
protected static $_driver = "mysql";
protected static $_timezone = "+0:00";

// The creation order of PHP.Gt tables (some may rely on others in foreign
// key constraints, for example).
protected $_sharedCreationOrder = array(
	"User",
	"Content",
	"Blog"
);

// The creation order of application specific tables. These will always be
// created *after* the PHP.Gt tables.
protected $_creationOrder = array(
);

public static function init() {
	static::$_name = isset(static::$_name)
		? static::$_name
		: "Gt_" . APPNAME;
	static::$_user = isset(static::$_user)
		? static::$_user
		: "Gt_" . APPNAME;
	static::$_pass = isset(static::$_pass)
		? static::$_pass
		: "Gt_" . APPNAME . "_Pass";
	//static::$_pass = md5(APPSALT . static::$_pass);
}

public static function getCreationOrder() {
	return array_merge(
		static::$_sharedCreationOrder,
		static::$_creationOrder);
}

public static function getSettings() {
	return array(
		"ConnectionString" => 
			static::$_driver 
			. ":dbname=" 	. static::$_name 
			. ";host=" 		. static::$_host
			. ";port=" 		. static::$_port
			. ";charset=" 	. static::$_charset,
		"ConnectionString_Root" =>
			static::$_driver
			. ":host=" . static::$_host,
		"Username"	=> static::$_user,
		"Password"	=> static::$_pass,
		"DbName" 	=> static::$_name,
		"Host"		=> static::$_host,
		"Timezone"	=> static::$_timezone,
	);
}

}#