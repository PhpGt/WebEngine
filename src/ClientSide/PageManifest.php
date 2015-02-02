<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Dom\Node;
use \Gt\Request\Request;
use \Gt\Response\Response;

class PageManifest extends Manifest {

public $fingerprint;
public $pathDetails;

private $sourceAttributeArray = ["src", "href"];

private $domHead;
private $request;
private $response;

private $nodeList;

// TODO: Make this static, utilise in calculateFingerprint().
private $sourceAttribute = [
	"SCRIPT" => "src",
	"LINK" => "href",
];

public static $xpathQuery =
	"./script[@src] | ./link[@rel = 'stylesheet' and (@href)]";

/**
 *
 */
public function __construct(Node $domHead, $request, $response) {
	$this->domHead = $domHead;
	$this->request = $request;
	$this->response = $response;

	$this->pathDetails = $this->generatePathDetails();
	$this->fingerprint = $this->calculateFingerprint($this->pathDetails);
	$this->pathDetails->setFingerprint($this->fingerprint);

	$meta = $this->domHead->ownerDocument->createElement("meta", [
		"name" => "fingerprint",
		"content" => $this->fingerprint,
	]);
	$this->domHead->appendChild($meta);
}

/**
 * Constructs a new PathDetails object from elements matching the manifest's
 * CSS query selector
 *
 * @return PathDetails A PathDetails object describing current dom head's
 * source paths and representing destination paths
 */
public function generatePathDetails() {
	$this->nodeList = $this->domHead->xpath(self::$xpathQuery);

	// Do not add nodes that reference the asset directory.
	foreach ($this->nodeList->nodeArray as $i => $node) {
		$source = $node->getAttribute(
			$this->sourceAttribute[$node->tagName]);
		$source = strtolower($source);
		if(strpos($source, "/asset") === 0) {
			unset($this->nodeList->nodeArray[$i]);
		}
	}
	$this->nodeList->nodeArray = array_values($this->nodeList->nodeArray);

	$pathDetails = new PathDetails($this->nodeList);
	return $pathDetails;
}

/**
 * Creates an MD5 hash representing the combined content and filenames of all
 * client side resorces represented in the dom head.
 *
 * @param PathDetails $pathDetails Representation of client-side files contained
 * within current dom head ready to fingerprint
 *
 * @return string MD5 hash representation of current dom head
 */
public function calculateFingerprint($pathDetails) {
	// The source fingerprint is a concatenation of all files' MD5s, which
	// in turn will be hashed to create an output MD5.
	$fingerprintSource = "";

	foreach ($pathDetails as $pathDetail) {
		$node = $pathDetail["node"];
		$nodeSourceAttriute = null;

		foreach ($this->sourceAttributeArray as $sourceAttributeValue) {
			if($node->hasAttribute($sourceAttributeValue)) {
				$nodeSourceAttriute = $sourceAttributeValue;
			}
		}

		$sourcePathUri = $node->getAttribute($nodeSourceAttriute);

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
		$sourcePathAbsolute = Path::fixCase($sourcePathAbsolute);

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

	$wwwPath = Path::get(Path::WWW);
	if(is_dir($wwwPath)) {
		foreach (new \DirectoryIterator($wwwPath) as $fileInfo) {
			if($fileInfo->isDot()) {
				continue;
			}

			$fileName = $fileInfo->getFilename();

			if(strpos(strtolower($fileName), "asset") === 0) {
				continue;
			}

			// Manifest-specific files are coppied into their own www
			// subdirectory with the fingerprint in the directory name. The
			// fileName ends in the manifest's fingerprint.
			if(substr($fileName, -strlen($this->fingerprint))
			=== $this->fingerprint) {
				$valid = true;
			}
		}
	}

	return $valid;
}

/**
 * Expands the DOM head to use fingerprinted and possibly compiled paths.
 */
public function expand() {
	foreach ($this->nodeList as $node) {
		$pathDetail = $this->pathDetails->getDetailForNode($node);
		$sourceAttribute = $this->sourceAttribute[$node->tagName];
		$node->setAttribute($sourceAttribute, $pathDetail["public"]);
	}
}

}#