<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Dom\Node;
use \Gt\Request\Request;
use \Gt\Response\Response;

class PageManifest extends Manifest {

private $fingerprint;
private $sourceAttributeArray = ["src", "href"];

private $domHead;
private $request;
private $response;

public static $xpathQuery =
	"./script[@src] | ./link[@rel = 'stylesheet' and (@href)]";

/**
 *
 */
public function __construct(Node $domHead, $request, $response) {
	$this->domHead = $domHead;
	$this->request = $request;
	$this->response = $response;

	$pathDetails = $this->generatePathDetails();
	$this->fingerprint = $this->calculateFingerprint($pathDetails);
}

/**
 * Constructs a new PathDetails object from elements matching the manifest's
 * CSS query selector
 *
 * @return PathDetails A PathDetails object describing current dom head's
 * source paths and representing destination paths
 */
public function generatePathDetails() {
	$nodeList = $this->domHead->xpath(self::$xpathQuery);

	$pathDetails = new PathDetails($nodeList);
	return $pathDetails;
}

/**
 * Creates an MD5 hash representing the combined content and filenames of all
 * client side resorces represented in the dom head.
 *
 * @param PathDetails $details Representation of client-side files contained
 * within current dom head ready to fingerprint
 *
 * @return string MD5 hash representation of current dom head
 */
public function calculateFingerprint($details) {
	// The source fingerprint is a concatenation of all files' MD5s, which
	// in turn will be hashed to create an output MD5.
	$fingerprintSource = "";

	foreach ($details->nodeList as $node) {
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
		// Do not add relative files to the fingerprint:
		else if(strpos($sourcePathUri, "/") > 0) {
			continue;
		}

		$sourcePathAbsolute =
				Path::get(Path::SRC)
				. $sourcePathUri;

		$fingerprintSource .= md5_file($sourcePathAbsolute);
	}

	if(empty($fingerprintSource)) {
		// Make it obvious if there is an empty dom head
		// (it's hard to remember the md5 of an empty string).
		return str_repeat("0", 32);
	}

	return md5($fingerprintSource);
}

/**
 * @param string $fingerprintToCheck Pass a fingerprint to check the current
 * DOMHead against. If null is given, checks the www path for public directory
 * presence.
 *
 * @return bool True if the files listed within the dom head are valid (having
 * the same filename and content), False if ANY of the files are invalid
 */
public function checkValid($fingerprintToCheck = null) {
	if(!is_null($fingerprintToCheck)) {
		return $this->calculateFingerprint($this->generatePathDetails())
			=== $fingerprintToCheck;
	}

	$valid = false;

	// TODO: Check files in www directory.

	return $valid;
}

}#