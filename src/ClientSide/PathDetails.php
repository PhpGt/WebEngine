<?php
/**
 * Represents a NodeList from the Dom Document - typically the dom head - that
 * can be traversed, producing a source / destination array for public assets.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Dom\Node;
use \Gt\Dom\NodeList;

class PathDetails implements \Iterator {

private $nodeList;
private $iteratorIndex = 0;
private $fingerprint = "{FINGERPRINT}";

private $sourceAttribute = [
	"SCRIPT" => "src",
	"LINK" => "href",
];

private $extensionMap = [
	"scss"		=> "css",
	"less"		=> "css",
	"ts"		=> "js",
	"coffee"	=> "js",
];

/**
 * @param NodeList|array $domNodeList List of elements to represent
 */
public function __construct($nodeList = []) {
	$this->nodeList = $nodeList;
}

/**
 *
 */
public function setFingerprint($fingerprint) {
	$this->fingerprint = $fingerprint;
}

/**
 * Get the Path Detail array for an individual node in the nodeList represented
 * by this object.
 *
 * @param Node $sourceNode Reference to a node within the current nodeList
 *
 * @return array The Path Detail array, or null if requested Node does not
 * exist in the current NodeList
 */
public function getDetailForNode(Node $sourceNode) {
	$detail = null;

	foreach ($this->nodeList as $i => $node) {
		if($node === $sourceNode) {
			$detail = $this->buildDetail($i);
			break;
		}
	}

	return $detail;
}

/**
 * Returns an associative array with four keys: "source", "destination",
 * "public" and "node".
 * The source key is an absolute file path to the source file on disk
 * represented by the Node in the PathDetails object.
 * The destination key is an absolute file path to the public file on disk.
 * The public key is the absolute URI as seen by the browser.
 * The node key is the underlying Dom Node represented by this object.
 *
 * @param int $index Numerical index for which Node in the NodeList to
 * represent
 *
 * @return array Associative array with two keys: source, destination
 */
private function buildDetail($index) {
	$node = $this->nodeList[$index];
	$source = $this->getSource($node);
	$destination = $this->getDestination($source);
	$public = $this->getPublic($destination);

	return [
		"source" => $source,
		"destination" => $destination,
		"public" => $public,
		"node" => $node,
	];
}

/**
 * Expands a Node's public-facing URI and returns the absolute disk path of
 * the file.
 *
 * @param Node $node The target Node
 *
 * @return string Absolute file path of source file
 */
private function getSource(Node $node) {
	$srcPath = Path::get(Path::SRC);
	$publicPath = $node->getAttribute($this->sourceAttribute[$node->tagName]);
	$sourcePath = Path::fixCase($srcPath . $publicPath);
	return $sourcePath;
}

/**
 * Returns the absolute destination public file path from the corresponding
 * source file path, rewriting the extension if the public file is compiled.
 * If the fingerprint has not been set for the PathDetails, a placeholder will
 * be present in the returned path: {FINGERPRINT}
 *
 * @param string $source Absolute file path to source
 *
 * @return string Absolute file path to destination
 */
private function getDestination($source) {
	$relativePath = substr($source, strlen(Path::get(Path::SRC)));
	$relativePath = Path::fixCase($relativePath);
	// Inject the fingerprint placeholder in place (before second slash).
	$relativePathFingerprint = substr_replace(
		$relativePath,
		"-" . $this->fingerprint,
		strpos($relativePath, "/", 1),
		0
	);
	$destinationPath = Path::get(Path::WWW);
	$destinationPath = Path::fixCase($destinationPath);
	$destinationPath .= $relativePathFingerprint;

	$destinationPath = $this->fixExtension($destinationPath);

	return $destinationPath;
}

/**
 * Replace the path extension with the corresponding extension in the
 * $extensionMap array, if one exists.
 *
 * @param string $path Input path
 *
 * @return string Path with corrected extension
 */
private function fixExtension($path) {
	$extension = pathinfo($path, PATHINFO_EXTENSION);
	$extension = strtolower($extension);

	if(!array_key_exists($extension, $this->extensionMap)) {
		return $path;
	}

	$path = substr($path, 0, strrpos($path, ".") + 1);
	$path .= $this->extensionMap[$extension];

	return $path;
}

/**
 * Returns the public (client-side) path from an absolute file path in the www
 * directory.
 *
 * @param string $path Absolute path to file within www directory
 *
 * @return string Absolute URI for use on the client-side
 */
private function getPublic($path) {
	$publicPath = $path;

	if(strpos($publicPath, Path::get(Path::WWW)) === 0) {
		$publicPath = substr($publicPath, strlen(Path::get(Path::WWW)));
	}

	return $publicPath;
}

// Iterator ////////////////////////////////////////////////////////////////////
public function current() {
	return $this->buildDetail($this->key());
}
public function key() {
	return $this->iteratorIndex;
}
public function next() {
	++$this->iteratorIndex;
}
public function rewind() {
	$this->iteratorIndex = 0;
}
public function valid() {
	return isset($this->nodeList[$this->iteratorIndex]);
}

}#