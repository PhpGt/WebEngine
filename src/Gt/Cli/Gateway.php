<?php
/**
 * Router script used by gtserver shell script. The gateway either returns the
 * bytes of a static file (when requested with a file extension) or passes the 
 * request on to the PHP.Gt Go initialiser object for processing.
 *
 * Will serve all directory-style URLs through PHP.Gt. Files (URLs with 
 * file extensions) will have their bytes streamed and correct HTTP headers set.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
require_once(__DIR__ . "/../../../vendor/autoload.php");

/**
 * 
 */
function isStaticFileRequest($uri) {
	$pathinfo_ext = pathinfo($uri, PATHINFO_EXTENSION);
	return !empty($pathinfo_ext);
}

function getAbsoluteFilePath($uri) {

}

function serveFile($filePath) {

}

if(php_sapi_name() === "cli-server") {
	$uri = $_SERVER["REQUEST_URI"];
	if(isStaticFileRequest($uri)) {
		$filePath = getAbsoluteFilePath();
		serveFile($filePath);
	}
	else {
		return new Gt\Core\Go();
	}
}