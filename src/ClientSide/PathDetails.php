<?php
/**
 * Represents a NodeList from the Dom Document - typically the dom head - that
 * can be traversed, producing a source / destination array for public assets.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Dom\NodeList;

class PathDetails implements Iterator {

private $nodeList;
private $iteratorIndex = 0;

/**
 * @param NodeList $domNodeList List of elements to represent
 */
public function __construct($nodeList = []) {
	$this->nodeList = $nodeList;
}

/**
 * Returns an associative array with two keys: source and destination. The
 * source key is an absolute file path to the source file on disk represented by
 * the Node in the PathDetails object, and the destination key is an absolute
 * file path to the public file on disk.
 *
 * @param int $index Numerical index for which Node in the NodeList to
 * represent
 *
 * @return array Associative array with two keys: source, destination
 */
private function buildDetail($index) {
	$node = $nodeList[$index];
	$source = $this->getSource($node);
	$destination = $this->getDestination($source);

	return [
		"source" => $source,
		"destination" => $destination,
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

}

/**
 * Returns the absolute destination public file path from the corresponding
 * source file path, rewriting the extension if the public file is compiled.
 *
 * @param string $source Absolute file path to source
 *
 * @return string Absolute file path to destination
 */
private function getDestination($source) {

}

// Iterator ////////////////////////////////////////////////////////////////////
public current() {
	return $this->buildDetail($this->iteratorIndex);
}
public key() {
	return $this->iteratorIndex;
}
public next() {
	++$this->iteratorIndex;
}
public rewind() {
	$this->iteratorIndex = 0;
}
public valid() {
	return isset($this->nodeList[$this->iteratorIndex]);
}

}#