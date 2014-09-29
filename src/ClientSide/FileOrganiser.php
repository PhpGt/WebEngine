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

/**
 * @param PathDetails $pathDetails Representation of client-side paths
 *
 * @return bool True if organiser has copied any files, false if no files have
 * been coppied
 */
public function organise($pathDetails = []) {
	$hasOrganisedAnything = false;

	if(!$this->manifest->checkValid()) {
		if($this->response->getConfigOption("client_minified")) {
			// Compile everything.
		}
		// Do copying of files...
		$this->copyCompile($pathDetails);
	}

	if(!$this->checkStaticFilesValid()) {
		// Copy static files.
	}

	return $hasOrganisedAnything;
}

/**
 * Performs the copying from source directories to the www directory, compiling
 * files as necessary. For example, source LESS files need to be compiled to
 * public CSS files in this process.
 */
private function copyCompile($pathDetails) {
	foreach ($pathDetails as $pathDetail) {
		file_put_contents(
			$pathDetail->destination,
			Compiler::parse($pathDetail->source)
		);
	}
}

/**
 * Checks all files within the Asset directory against the www/Asset directory,
 * as well as checking only the static files within the Style directory against
 * the www/Style directory.
 */
private function checkStaticFilesValid() {
	// die(__FUNCTION__ . __FILE__);
}

}#