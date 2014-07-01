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
namespace Gt\Cli;

class Gateway {

/**
 * 
 */
public static function isStaticFileRequest($uri) {
	$pathinfo_ext = pathinfo(strtok($uri, "?"), PATHINFO_EXTENSION);
	return !empty($pathinfo_ext);
}

/**
 * 
 */
public static function getAbsoluteFilePath($uri) {
	return $_SERVER["DOCUMENT_ROOT"] . $uri;
}

/**
 * 
 */
public static function serveStaticFile($filePath) {
	if(!file_exists($filePath)) {
		throw new \Gt\Response\NotFoundException();
	}

	return readfile($filePath);
}

public static function serveDynamicRequest() {
	return new Gt\Core\Go();
}

}#
