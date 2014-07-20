<?php
/**
 * Used by Gatekeeper (procedural code) to decide whether to serve static files'
 * bytes, or to begin the request / response within PHP.Gt.
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class Gateway {

/**
 * Serves the requested Uri by first detecting whether to serve the static 
 * file or construct the given PHP.Gt Go class.
 * 
 * @param string $uri Uri of request
 * @param string $phpgt Name of the class to use that serves dynamic requests
 * @return mixed An instance of the $phpgt class
 */
public static function serve($uri, $phpgt = "\Gt\Core\Go") {
	if(self::isStaticFileRequest($uri)) {
		$filePath = self::getAbsoluteFilePath($uri);
		self::serveStaticFile($filePath);
	}
	else {
		return new $phpgt;
	}
}

/**
 * If the requested file exists within the www directory, it should be served
 * as a static file.
 * 
 * @param string $uri Uri of request
 * @return bool True if file request is to be treated as static.
 */
public static function isStaticFileRequest($uri) {
	return is_file(self::getAbsoluteFilePath($uri));
}

/**
 * Gets the absolute filepath to the static file requested by given uri.
 * File may not actually exist on disk.
 * 
 * @param string $uri Absolute uri of request.
 * @return string Absolute filepath.
 */
public static function getAbsoluteFilePath($uri) {
	return $_SERVER["DOCUMENT_ROOT"] . $uri;
}

/**
 * Streams the raw bytes of a static file to STDOUT.
 * 
 * @throws \Gt\Response\NotFoundException if the file does not exist on disk.
 * @param string $filePath Absolute path to file on disk.
 * 
 * @return int The number of bytes served.
 */
public static function serveStaticFile($filePath) {
	return readfile($filePath);
}

}#
