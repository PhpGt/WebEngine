<?php
/**
 * TODO: Docs
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

use \Gt\Response\ResponseContent;

class Document extends ResponseContent implements \ArrayAccess {

const DEFAULT_HTML = "<!doctype html>";
public $domDocument;
public $node;

private static $currentDocument = null;

/**
 * Passing in the HTML to parse as an optional first parameter automatically
 * calls the load function with provided HTML content.
 */
public function __construct($html = null) {
	$this->domDocument = new \DOMDocument("1.0", "utf-8");

	if(!is_null($html)) {
		if(empty($html)) {
			$html = self::DEFAULT_HTML;
		}
		$this->load($html);
	}

	$this->node = new Node($this->domDocument);

	if(is_null(self::$currentDocument)) {
		self::$currentDocument = $this;
	}
}

/**
 *
 */
public static function getCurrent() {
	return self::$currentDocument;
}

/**
 *
 */
public function __toString() {
	return $this->domDocument->saveHTML();
}

/**
 * Allows unserialization of one or more HTML files.
 * @param string|array $content A string of raw-HTML, or an array of strings
 * containing raw-HTML to concatenate and unserialize.
 */
public function load($content = null) {
	$string = "";

	if(is_null($content)) {
		$content = self::DEFAULT_HTML;
	}

	if(!is_array($content)) {
		$content = [$content];
	}

	foreach ($content as $c) {
		$string .= $c . PHP_EOL;
	}

	libxml_use_internal_errors(true);

	$string = mb_convert_encoding(trim($string), "HTML-ENTITIES", "utf-8");
	$this->domDocument->loadHTML($string);
}

/**
 *
 */
public function __call($name, $args) {
	if(is_callable([$this->node, $name])) {
		$result = call_user_func_array([$this->node, $name], $args);
		// TODO: Wrap result with Node or NodeList.
		return $result;
	}
	else {
		throw new NodeMethodNotDefinedException();
	}
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
	return call_user_func_array([$this->node, __FUNCTION__], func_get_args());
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
	return call_user_func_array([$this->node, __FUNCTION__], func_get_args());
}

/**
 * Used to replace a NodeList with another, via matching CSS selector.
 *
 * @param string $offset CSS selector string
 * @param NodeList $value A NodeList to replace the current one with
 */
public function offsetSet($offset, $value) {
	return call_user_func_array([$this->node, __FUNCTION__], func_get_args());
}

/**
 * Used to remove a NodeList, via matching CSS selector.
 *
 * @param string $offset CSS selector string
 */
public function offsetUnset($offset) {
	return call_user_func_array([$this->node, __FUNCTION__], func_get_args());
}

}#