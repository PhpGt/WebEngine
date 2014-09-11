<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Dom\Node;

class PageManifest extends Manifest {

private $fingerprint;
private $sourceAttributeArray = ["src", "href"];

/**
 *
 */
public function __construct(Node $domHead,
Request $request, Response $response) {
	$this->request = $request;
	$this->response = $response;

	$this->fingerprint = $this->calculateFingerprint($domHead);
	$this->pathDetails = $this->generatePathArray($domHead);
}

/**
 * Creates an MD5 hash representing the combined content and filenames of all
 * client side resorces represented in the dom head.
 *
 * @return string MD5 hash representation of current dom head
 */
public function calculateFingerprint(Node $domHead) {
	$nodeList = $domHead->querySelectorAll(
		"script[src], link[rel='stylesheet'][href]");
	// The source fingerprint is a concatenation of all files' MD5s, which
	// in turn will be hashed to create an output MD5.
	$fingerprintSource = "";

	foreach ($nodeList as $node) {
		$nodeSourceAttriute = null;

		foreach ($this->sourceAttributeArray as $sourceAttributeValue) {
			if($node->hasAttribute($sourceAttributeValue)) {
				$nodeSourceAttriute = $sourceAttributeValue;
			}
		}

		$sourcePathUri = $node->getAttribute($nodeSourceAttriute);
		$sourcePathAbsolute = "";

		// Do not add external files to the fingerprint:
		if(strstr($sourcePathUri, "//")) {
			continue;
		}
		// If the source path is an absolute URI, simply concatenate:
		else if(strpos($sourcePathUri, "/")) {
			$sourcePathAbsolute =
				Path::get(Path::SRC)
				. $sourcePathUri;
		}
		// If the source path is a relative URI, current URI's directory path
		// needs to be added to the absolute source path:
		else {
			$uriDirectory = pathinfo($this->request->uri, PATHINFO_DIRNAME);

			$sourcePathAbsolute =
				Path::get(Path::SRC)
				. $uriDirectory
				. $sourcePathUri;
		}

		$fingerprintSource .= md5_file($sourcePathAbsolute);
	}

	return md5($fingerprintSource);
}

/**
 *
 * @return PathDetails A PathDetails object describing current dom head's
 * source paths and representing destination paths
 */
public function generatePathArray(Node $domHead) {

}

/**
 *
 * @return bool True if the files listed within the dom head are valid (having
 * the same filename and content), False if ANY of the files are invalid
 */
public function checkValid() {

}
}#