<?php
/**
 * TODO: Docs
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Response\ResponseContent;
use \Gt\ClientSide\PageManifest;

class Document extends ResponseContent {

const DEFAULT_HTML = "<!doctype html>";
public $domDocument;
public $node;
public $nodeMap = [];

/**
 * Passing in the HTML to parse as an optional first parameter automatically
 * calls the load function with provided HTML content.
 *
 * @param string|DOMDocument $source Raw HTML string, or existing native
 * DOMDocument to represent
 */
public function __construct($source = null) {
	if($source instanceof \DOMDocument) {
		$this->domDocument = $source;
	}
	else {
		$this->domDocument = new \DOMDocument("1.0", "utf-8");

		if(!is_null($source)) {
			if(empty($source)) {
				$source = self::DEFAULT_HTML;
			}
			$this->load($source);
		}
	}

	if(!isset($this->node)) {
		$this->node = new Node($this, $this->domDocument);
	}

	// Store a self-reference in the native DOMDocument, for access within the
	// Node class.
	$this->domDocument->document = $this;
	$uuid = uniqid("nodeMap-", true);
	$this->domDocument->uuid = $uuid;
}

public function createManifest(Request $request, Response $response) {
	$domHead = $this->querySelector("head");
	return new PageManifest($domHead, $request, $response);
}

/**
 * Get reference to the Node object representing the provided DOMNode. Use this
 * instead of constructing a new Node each time you want access to the node to
 * avoid unnecessary memory usage.
 *
 * @param DOMNode|Node $domNode The provided DOMNode (or Node object) to obtain
 * a reference to
 *
 * @return Node A Node object representing the provided DOMNode. The object may
 * have already existed, or may have been constructed here for the first time
 */
public function getNode($domNode) {
	if($domNode instanceof Node) {
		$domNode = $domNode->domNode;
	}
	else if(!$domNode instanceof \DOMNode) {
		throw new InvalidNodeTypeException();
	}

	$node = null;

	// If the DOMNode has been used before, it will have a UUID property
	// attached, but there still may not be a Node object stored in the nodeMap,
	// so another check is required.
	if(!empty($domNode->uuid)) {
		if(isset($this->nodeMap[$domNode->uuid])) {
			$node = $this->nodeMap[$domNode->uuid];
		}
	}

	// Only construct a new Node object if there isn't one referencing the
	// provided DOMNode.
	if(is_null($node)) {
		if($domNode instanceof \DOMDocument) {
			$node = $domNode->document;
		}
		else {
			$node = new Node($this, $domNode);
		}
	}

	return $node;
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
	$result = call_user_func_array([$this->node, $name], $args);
	return $result;
}

public function __get($name) {
	$value = null;

	if(property_exists($this->domDocument, $name)) {
		$value = Node::wrapNative($this->domDocument->$name);
	}
	else {
		throw new InvalidNodePropertyException($name);
	}

	return $value;
}

}#