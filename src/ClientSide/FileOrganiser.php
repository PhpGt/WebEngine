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
		$hasOrganisedAnything = true;
		$passThrough = null;
		if($this->response->getConfigOption("client_minified")) {
			// Minify everything in www
			$passThrough = [new Minifier(), "minify"];
		}

		// Do copying of files...
		$this->copyCompile($pathDetails, $passThrough);
	}

	if(!$this->checkStaticFilesValid()) {
		// Copy static files.
		$hasOrganisedAnything = true;
	}

	return $hasOrganisedAnything;
}

/**
 * Performs the copying from source directories to the www directory, compiling
 * files as necessary. For example, source LESS files need to be compiled to
 * public CSS files in this process.
 *
 * @param PathDetails $pathDetails
 * @param callable|null $callback The callable to pass output through before
 * writing to disk
 */
private function copyCompile($pathDetails, $callback = null) {
	foreach ($pathDetails as $pathDetail) {
		if(!is_dir(dirname($pathDetail["destination"]))) {
			mkdir(dirname($pathDetail["destination"]), 0775, true);
		}

		$output = Compiler::parse($pathDetail["source"]);
		if(!is_null($callback)) {
			$output = call_user_func_array($callback, [$output]);
		}

		file_put_contents(
			$pathDetail["destination"],
			$output
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
	return true;
}

}#