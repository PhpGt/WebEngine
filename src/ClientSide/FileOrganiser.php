<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Response\Response;
use \Gt\Core\Path;

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
	$copyCount = 0;

	if(!$this->manifest->checkValid()) {
		$passThrough = null;
		$callback = null;
		if($this->response->getConfigOption("client_minified")) {
			// Minify everything in www
			$callback = [new Minifier(), "minify"];
		}

		// Do copying of files...
		$copyCount += $this->copyCompile($pathDetails, $callback);
	}

	if(!$this->checkAssetValid()) {
		$copyCount += $this->copyAsset();
	}

	return !!($copyCount);
}

/**
 * Performs the copying from source directories to the www directory, compiling
 * files as necessary. For example, source LESS files need to be compiled to
 * public CSS files in this process.
 *
 * @param PathDetails $pathDetails
 * @param callable|null $callback The callable to pass output through before
 * writing to disk
 *
 * @return int Number of files copied
 */
public function copyCompile($pathDetails, $callback = null) {
	$copyCount = 0;

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
		++$copyCount;
	}

	return $copyCount;
}

/**
 * Fingerprints the source Asset directory contents and compares to the
 * fingerprint cache in the www directory.
 *
 * @return bool True if the www asset directory is valid, false if it is not
 * (or if it doesn't exist)
 */
public function checkAssetValid() {
	$wwwDir = Path::get(Path::WWW);
	$assetSrcDir = Path::get(Path::ASSET);
	$assetWwwDir = $wwwDir . "/" . substr($assetSrcDir, -strlen("asset"));
	$assetWwwFingerprintFile = $wwwDir . "/asset-fingerprint";

	if(!is_dir($assetWwwDir)
	|| !file_exists($assetWwwFingerprintFile)) {
		return false;
	}


	// Recursive fingerprint whole source directory.
	$assetWwwFingerprint = file_get_contents($assetWwwFingerprintFile);
	$assetSrcFingerprint = $this->recursiveFingerprint($assetSrcDir);

	return ($assetWwwFingerprint === $assetSrcFingerprint);
}

/**
 *
 */
public function copyAsset() {
	$copyCount = 0;
	return $copyCount;
}

/**
 * Recursively iterate over all files within given directory and build up a
 * hash of their contents and file names.
 *
 * @param string $dir Directory to iterate
 *
 * @return string 32 character hash of directory's contents, or 32 zeros
 * indicating an empty or non-existant directory
 */
private function recursiveFingerprint($dir) {
	$emptyHash = str_pad("", 32, "0");
	$hash = "";

	if(!is_dir($dir)) {
		return $emptyHash;
	}

	foreach ($iterator = new \RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir,
		RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item) {
		$path = $item->getPathname();
		$hash .= md5($path) . md5_file($path);
	}

	if(strlen($hash) === 0) {
		return $emptyHash;
	}

	return md5($hash);
}

}#