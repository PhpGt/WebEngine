<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\Dom;

class Node implements ArrayAccess {

private $domElement;

/**
 *
 */
public function __construct(Document $document, $domNode,
array $attributeArray = [], $value = null) {
	if($domNode instanceof Node) {
		$this->domElement = $domNode->domElement;
	}
	else if($domNode instanceof DOMElement) {
		$this->domElement = $domNode;
	}
	else if(is_string($domNode)) {
		$this->domElement =
	}
	else {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException();
	}

	foreach ($attributeArray as $key => $value) {
		$this->domElement->setAttribute($key, $value);
	}
}

/**
 * Returns the first element within the document (using depth-first
 * pre-order traversal of the document's nodes) that matches the specified
 * group of selectors.
 *
 * @param string $query A string containing one or more CSS selectors,
 * separated by commas
 *
 * @return Node|null Returns null if no matches are found; otherwise, it
 * returns the first matching element.
 */
public function querySelector($query) {
	$nodeList = $this->css($query, null, 1);
	if($nodeList->length > 0) {
		return $nodeList;
	}

	return null;
}

/**
 * Returns a list of the elements within the document (using depth-first
 * pre-order traversal of the document's nodes) that match the specified
 * group of selectors.
 *
 * @param string $query A string containing one or more CSS selectors,
 * separated by commas
 *
 * @return NodeList A NodeList with 0 or more matching elements
 */
public function querySelectorAll($query) {
	return $this->css($query);
}

public function css($query, $context = null, $max = 0) {

}

public function xpath($query, $context = null, $max = 0) {

}

// ArrayAccess -----------------------------------------------------------------

/**
 * This method is executed when using isset() or empty()
 *
 * @param string $offset CSS selector string
 *
 * @return bool True if the provided CSS selector string matches 1 or more
 * elements in the current Document
 */
public function offsetExists($offset) {
	$matches = $this->css($offset);
	return ($matches->length > 0);
}

/**
 * Wrapper to the Document::css() method, allowing the DOM to be CSS queried
 * using array notation.
 *
 * @param string $offset CSS selector string
 *
 * @return NodeList A NodeList with 0 or more matching elements
 */
public function offsetGet($offset) {
	return $this->css($offset);
}

/**
 * Used to replace a NodeList with another, via matching CSS selector.
 *
 * @param string $offset CSS selector string
 * @param NodeList $value A NodeList to replace the current one with
 */
public function offsetSet($offset, $value) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

/**
 * Used to remove a NodeList, via matching CSS selector.
 *
 * @param string $offset CSS selector string
 */
public function ofsetUnSet($offset) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

}#