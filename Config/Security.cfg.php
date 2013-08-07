<?php class Security_Config_Framework extends Config {
protected static $_remoteSetupWhitelist = array(
	"127.0.0.1"
);
protected static $_allowAllRemoteAdmin = true;
protected static $_remoteAdminWhiteList = array(
	"127.0.0.1"
);
protected static $_salt = "Php.Gt default salt - please change this!";
protected static $_domain;
protected static $_remoteIp;

public static function init() {
	define("APPSALT", hash("sha512", static::$_salt));
	static::$_remoteIp = isset($_SERVER["REMOTE_ADDR"])
		? $_SERVER["REMOTE_ADDR"]
		: null;
	static::$_domain = isset(static::$_domain)
		? static::$_domain
		: isset($_SERVER["HTTP_HOST"])
			? $_SERVER["HTTP_HOST"]
			: null;
}

public static function getDomain() {
	return static::$_domain;
}

public static function isSetupAllowed() {
	return in_array(static::$_remoteIp, static::$_remoteSetupWhitelist);
}

public static function isAdminAllowed() {
	return in_array(static::$_remoteIp, static::$_remoteAdminWhiteList)
		|| static::$_allowAllRemoteAdmin;
}

}#