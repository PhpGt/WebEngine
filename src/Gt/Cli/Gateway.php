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
 * Streams the raw bytes of a static file to STDOUT or a string if returnBytes
 * is set to true.
 * 
 * @throws \Gt\Response\NotFoundException if the file does not exist on disk.
 * @param string $filePath Absolute path to file on disk.
 * @param bool $returnBytes Optional. Set to true to return the contents of 
 * the file as a string of bytes, rather than the default streaming to STDOUT.
 * Defaults to false.
 * 
 * @return int|string The number of bytes served, or the bytes as a string if
 * $returnBytes is set to true.
 */
public static function serveStaticFile($filePath, $returnBytes = false) {
	if(!file_exists($filePath)) {
		throw new \Gt\Response\NotFoundException();
	}

	if($returnBytes) {
		ob_start();
	}
	
	$bytesRead = readfile($filePath);

	if($returnBytes) {
		$bytes = ob_get_contents();
		ob_end_clean();
		return $bytes;
	}
	else {
		return $bytesRead;
	}
}

/**
 * Passes the request to the core Go class, used to carry out the whole 
 * request / response pipeline.
 * 
 * @return \Gt\Core\Go New instance of the Go class, for debugging purposes.
 */
public static function serveDynamicRequest() {
	return new \Gt\Core\Go();
}

}#
