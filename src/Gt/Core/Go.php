<?php
/**
 * Acts as the entry point for serving PHP.Gt applications. Only intended for
 * serving dynamic responses. The webserver should be set up to handle serving
 * static files. When using PHP.Gt's inbuilt server (gtserver), Gateway.php
 * serves static files before instantiating this class.
 * 
 * Go has three responsibilities:
 * 1) Define named constants used across PHP.Gt and its applications.
 * 2) Initialise configuration.
 * 3) Initialise dispatcher, depending on type of request. 
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;
final class Go {

public function __construct() {
	$this->startTime = microtime();
	if(empty($_SERVER)) {
		throw new \Gt\Core\Exception\UndefinedVariableException(
			"\$_SERVER is not defined. Are you running from cli?");
	}

	$this->defineConstants();
	$this->initialiseConfiguration();
	$this->initialiseDispatcher();
}

/**
 * Defines all constants used throughout PHP.Gt and its applications. These 
 * include: 
 * * GTROOT 	- the absolute path to the `Gt` directory.
 * * APPROOT 	- the absolute path to the application's root directory.
 * * DOCROOT 	- the absolute path to the application's www directory.
 * * PATH 		- the absolute requested pathname.
 * * STARTTIME	- the unix time when Go was called, in milliseconds.
 */
private function defineConstants() {
	$gtroot = dirname(__DIR__);
	$approot = dirname($_SERVER["DOCUMENT_ROOT"]);
	$path = strtok($_SERVER["REQUEST_URI"], "?");

	define("GTROOT", $gtroot);
	define("APPROOT", $approot);
	define("DOCROOT", $_SERVER["DOCUMENT_ROOT"]);
	define("PATH", $path);
	define("STARTTIME", $this->startTime);
}

private function initialiseConfiguration() {
	throw new Exception\NotImplementedException();
}

private function initialiseDispatcher() {
	throw new Exception\NotImplementedException();
}

}#