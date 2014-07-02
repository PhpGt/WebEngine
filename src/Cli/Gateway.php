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

public static function serve($uri, $phpgt = "\Gt\Core\Go") {
	if(self::isStaticFileRequest($_SERVER["REQUEST_URI"])) {
		$filePath = self::getAbsoluteFilePath($_SERVER["REQUEST_URI"]);
		self::serveStaticFile($filePath);
	}
	else {
		new $phpgt;
	}
}

/**
 * Returns whether the requested uri shall be treated as a static file.
 * Static files are intended to be served directly by the webserver, rather than
 * dynamically created within PHP.Gt.
 * 
 * @param string $uri Absolute uri of request
 * @return bool True if file request is to be treated as static.
 */
public static function isStaticFileRequest($uri) {
	$pathinfo_ext = pathinfo(strtok($uri, "?"), PATHINFO_EXTENSION);
	return !empty($pathinfo_ext);
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
	if(!file_exists($filePath)) {
		throw new \Gt\Response\NotFoundException();
	}

	return readfile($filePath);
}

}#
