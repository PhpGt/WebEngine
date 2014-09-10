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
public function __construct(Node $domHead) {
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

	foreach ($nodeList as $node) {
		$sourceAttribute = null;

		foreach ($this->sourceAttributeArray as $key => $value) {
			// TODO: Save sourceattr
		}
	}
	var_dump($nodeList);die();
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