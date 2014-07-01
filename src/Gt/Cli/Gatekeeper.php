<?php
/**
 * This script is intended to be run as the inbuilt webserver's "router" script.
 * Procedural code below is necessary when triggered from inbuilt webserver
 * as this script is the point-of-entry for the whole application.
 * 
 * Uses Gateway class to decide whether to serve static content or begin 
 * request / response within PHP.Gt.
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
require_once(__DIR__ . "/../../../vendor/autoload.php");

// Only allow this script to be invoked from inbuilt webserver.
if(php_sapi_name() === "cli-server") {
	if(Gateway::isStaticFileRequest($_SERVER["REQUEST_URI"])) {
		$filePath = Gateway::getAbsoluteFilePath($_SERVER["REQUEST_URI"]);
		Gateway::serveStaticFile($filePath);
	}
	else {
		return Gateway::serveDynamicRequest();
	}
}
else {
	throw new InvalidContextException(php_sapi_name());
}