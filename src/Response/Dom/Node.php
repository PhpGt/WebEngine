<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\Dom;

use Symfony\Component\CssSelector\CssSelector;

class Node implements \ArrayAccess {

public $document;
public $domNode;

/**
 *
 */
public function __construct(Document $document, $domNode,
array $attributeArray = [], $value = null) {
	if($domNode instanceof Node) {
		$this->domNode = $domNode->domNode;
	}
	else if($domNode instanceof \DOMNode) {
		$this->domNode = $domNode;
	}
	else if(is_string($domNode)) {
		// $this->domNode =
	}
	else {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException();
	}

	foreach ($attributeArray as $key => $value) {
		$this->domNode->setAttribute($key, $value);
	}

	$this->document = $document;
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
	$nodeList = $this->css($query, null);
	if($nodeList->length > 0) {
		// TODO: Might be possible to speed this up?
		return $nodeList[0];
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

/**
 *
 */
public function css($query, $context = null) {
	$context = $this->checkContext($context);

	$xpath = CssSelector::toXPath($query);
	return $this->xpath($xpath, $context);
}

/**
 *
 */
public function xpath($query, $context = null) {
	$context = $this->checkContext($context);

	$xpath = new \DOMXPath($this->document->domDocument);
	return $xpath->query($query, $context);
}

/**
 * Ensures the provided context is of a native DOMDocument type rather than
 * an enhanced object, so it can be used with native DOMDocument methods.
 *
 * @param Node|DOMNode|null $context The current context. If null is provided,
 * this current Node's DOMNode is used.
 *
 * @return DOMNode The contextual DOMNode
 */
public function checkContext($context) {
	if(is_null($context)) {
		$context = $this->domNode->documentElement;
	}

	if($context instanceof Node) {
		$context = $context->domNode;
	}
	else if(!$context instanceof \DOMNode) {
		throw new InvalidNodeTypeException();
	}

	return $context;
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
public function offsetUnset($offset) {
	throw new \Gt\Core\Exception\NotImplementedException();
}

}#