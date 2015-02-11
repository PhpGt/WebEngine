<?php
/**
 * NodeList objects are collections of nodes such as those returned by
 * Node->childNodes and the querySelectorAll method.
 *
 * This class is an extension to the native DOMNodeList present in PHP, aiming
 * to provide a DOM-level-4-capable interface by defining missing methods and
 * properties.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

/**
 * //property <type> name
 *
 * @method Node item(int $index)
 */
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
		throw new InvalidNodeTypeException(gettype($domNodeList));
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
	if(!$value instanceof Node) {
		throw new InvalidNodeTypeException(gettype($value));
	}
	$this->nodeArray[$offset] = $value;
}

/**
 *
 */
public function offsetUnset($offset) {
	unset($this->nodeArray[$offset]);
}

}#