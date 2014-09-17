<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Response\Response;

class FileOrganiser {

private $response;
private $manifest;

public function __construct($response, Manifest $manifest) {
	$this->response = $response;
	$this->manifest = $manifest;
}

public function organise() {
	if(!$this->manifest->checkValid()) {
		// if($this->response->getConfigOption("client_compiled")) {
		// 	// Compile everything.
		// }
		// Do copying of files...
	}

	if(!$this->checkStaticFilesValid()) {
		// Copy static files.
	}
}

/**
 * Checks all files within the Asset directory against the www/Asset directory,
 * as well as checking only the static files within the Style directory against
 * the www/Style directory.
 */
public function checkStaticFilesValid() {
	// die(__FUNCTION__ . __FILE__);
}

}#