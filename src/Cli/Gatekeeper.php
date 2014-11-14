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

$autoloader = realpath(__DIR__ . "/../../../../autoload.php");
if(false === $autoloader) {
	// Assume installed globally.
	$autoloader = realpath(__DIR__ . "/../../vendor/autoload.php");

}

if(false === $autoloader) {
	die("Composer autoloader missing. Have you installed correctly?");
}
require($autoloader);

// Only allow this script to be invoked from inbuilt webserver.
$sapi = php_sapi_name();

switch($sapi) {
case "cli-server":
	return Gateway::serve($_SERVER["REQUEST_URI"]);

case "cli":
	throw new InvalidContextException(php_sapi_name());

default:
	// When using third-party webserver:
	return new \Gt\Core\Start($_SERVER["REQUEST_URI"]);
}