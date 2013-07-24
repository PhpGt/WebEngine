<?php class App_Config_Framework extends Config {
/**
 * All config classes suffixed with _Framework hold configuration settings
 * that apply to all applications. All protected properties can be overridden
 * by application-specific versions of the class. App-specific versions are
 * stored in the Config application directory, and the classes are named
 * similarly, but drop the _Framework suffix.
*/
// When an application is set to production mode, errors will be less
// verbose and less debugging information is available.
protected static $_isProduction = false;
protected static $_isCached = false;
protected static $_isClientCompiled = false;
protected static $_timezone = "UTC";

// When true, URLs are converted into directory style, dropping the need
// for the file extension.
protected static $_directoryUrls = false;
protected static $_reserved = array("Gt");

public static function init() { }

public static function isCached() {
	return static::$_isCached;
}

public static function getReserved() {
	return static::$_reserved;
}

public static function getTimezone() {
	return static::$_timezone;
}

public static function isClientCompiled() {
	return static::$_isClientCompiled;
}

public static function isProduction() {
	return static::$_isProduction;
}

}#