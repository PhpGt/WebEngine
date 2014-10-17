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
 * been copied
 */
public function organise($pathDetails = []) {
	$copied = false;

	if(!$this->manifest->checkValid()) {
		$passThrough = null;
		$callback = null;
		if($this->response->getConfigOption("client_minified")) {
			// Minify everything in www
			$callback = [new Minifier(), "minify"];
		}

		// Do copying of files...
		$copied = $this->copyCompile($pathDetails, $callback);
	}

	if(!$this->checkAssetValid()) {
		$this->copyAsset();
	}

	return $copied;
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
public function copyCompile($pathDetails, $callback = null) {
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
 *
 */
public function checkAssetValid() {

}

/**
 *
 */
public function copyAsset() {

}

}#