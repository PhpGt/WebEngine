<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

class NodeList implements \Countable, \Iterator, \ArrayAccess {

private $iteratorIndex = 0;
public $nodeArray;

public function __construct($domNodeList) {
	$this->nodeArray = [];

	if($domNodeList instanceof NodeList
	|| $domNodeList instanceof \DOMNodeList
	|| is_array($domNodeList)) {
		foreach($domNodeList as $domNode) {
			// Get reference to current DOMNode's owner document, as this may
			// be different from node to node.
			if($domNode instanceof Node) {
				// Ensure we are working with a native DOMNode.
				$domNode = $domNode->domNode;
			}

			$domDocument = $domNode->ownerDocument;

			// Call getNode to get reference to a Node object representing the
			// given DOMNode, then push it into the nodeArray.
			$node = $domDocument->document->getNode($domNode);
			$this->nodeArray []= $node;
		}
	}
	else {
		throw new InvalidNodeTypeException();
	}
}

// Countable -------------------------------------------------------------------
/**
 * Count the number of Node elements stored within this NodeList's $nodeArray.
 *
 * @return integer Number of elements contained by this NodeList
 */
public function count() {
	return count($this->nodeArray);
}

// Iterator --------------------------------------------------------------------
/**
 *
 */
public function rewind() {
	$this->iteratorIndex = 0;
}

/**
 *
 */
public function valid() {
	return isset($this->nodeArray[$this->iteratorIndex]);
}

/**
 *
 */
public function current() {
	return $this->nodeArray[$this->iteratorIndex];
}

/**
 *
 */
public function key() {
	return $this->iteratorIndex;
}

/**
 *
 */
public function next() {
	++$this->iteratorIndex;
}

// ArrayAccess -----------------------------------------------------------------

/**
 *
 */
public function offsetExists($offset) {
	return isset($this->nodeArray[$offset]);
}

/**
 *
 */
public function offsetGet($offset) {
	return $this->nodeArray[$offset];
}

/**
 *
 */
public function offsetSet($offset, $value) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

/**
 *
 */
public function offsetUnset($offset) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

}#