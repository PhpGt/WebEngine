<?php
/**
 * Used by Gatekeeper (procedural code) to decide whether to serve static files'
 * bytes, or to begin the request / response within PHP.Gt.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;

use \Gt\Response\Headers;
use \Gt\Response\ResponseCode;

class Gateway {

/**
 * Serves the requested Uri by first detecting whether to serve the static
 * file or construct the given bootstrapping class.
 *
 * @param string $uri Uri of request
 * @param string $phpgt Name of the class to use that serves dynamic requests
 * @return mixed An instance of the $phpgt class
 */
public static function serve($uri, $phpgt = "\Gt\Core\Start") {
	if(self::isStaticFileRequest($uri)) {
		$filePath = self::getAbsoluteFilePath($uri);
		self::serveStaticFile($filePath);
	}
	else {
		return new $phpgt($uri);
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
	$ext = pathinfo($filePath, PATHINFO_EXTENSION);
	if(isset(Server::$contentType[$ext])) {
		$mime = Server::$contentType[$ext];
	}
	else {
		$finfo = new \Finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($filePath);
	}

	Headers::add("Content-type", $mime);
	Headers::send(new ResponseCode);
	return readfile($filePath);
}

}#
