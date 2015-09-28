<?php
/**
 * Each web page loaded in the browser has its own document object.
 * The Document interface serves as an entry point into the web page's
 * content (the DOM tree, including elements such as <body> and <table>)
 * and provides functionality which is global to the document (such as
 * creating new elements in the document).
 *
 * This class is an extension to the native DOMDocument present in PHP, aiming
 * to provide a DOM-level-4-capable interface by defining missing methods and
 * properties.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Response\ResponseContent;
use \Gt\ClientSide\PageManifest;

/**
 * Note: The Document interface also inherits properties and methods from the
 * Node interface.
 *
 * @property-read string $actualEncoding
* @property NodeList $anchors
 * @property Node $body
 * @property-read DOMConfiguration $config
 * @property-read DOMConfiguration $config
 * @property Document $document
 * @property DOMDocument $documentElement
 * @property string $documentURI
 * @property string $encoding
* @property Node $firstChild
 * @property bool $formatOutput
* @property NodeList $forms
 * @property Node $head
* @property NodeList $images
 * @property-read DOMImplementation $implementation
* @property NodeList $links
 * @property bool $preserveWhiteSpace
 * @property bool $recover
* @property string $referrer
 * @property bool $resolveExternals
 * @property bool $standalone
 * @property bool $strictErrorChecking
 * @property bool $substituteEntities
* @property string textContent
* @property string $title
 * @property bool $validateOnParse
 * @property string $value
 * @property string $version
 * @property-read string $xmlEncoding
 * @property bool $xmlStandalone
 * @property string $xmlVersion
 *
 * @method Node adoptNode(Node $externalNode)
 * @method Node appendChild(Node $newNode)
 * @method Node cloneNode(bool $deep)
 * @method DOMAttr createAttribute(string $name)
 * @method DOMCDATASection createCDATASection(string $data)
 * @method DOMComment createComment(string $data)
 * @method DOMDocumentFragment createDocumentFragment()
 * @method Node createElement(string $tagName [, array $attributeArray, string $nodeValue])
 * @method DOMEntityReference createEntityReference(string $name)
 * @method DOMProcessingInstruction createProcessingInstruction(string $target [, string $data])
 * @method DOMText createTextNode(string $content)
 * @method Node getElementById(string $ID)
* @method NodeList getElementsByClassName(string $name)
* @method NodeList getElementsByName(string $name)
 * @method NodeList getElementsByTagName(string $name)
 * @method int getLineNo()
 * @method string getNodePath()
 * @method bool hasAttributes()
 * @method bool hasChildNodes()
 * @method Node insertBefore(Node $newNode, Node $refNode)
 * @method Node importNode(Node $importedNode [, bool $deep])
 * @method bool isDefaultNamespace(string $namespaceURI)
 * @method bool isSameNode(Node $node)
 * @method bool isSupported(string $feature, string $version)
 * @method string lookupNamespaceURI(string $prefix)
 * @method string lookupPrefix($namespaceURI)
 * @method void normalize()
 * @method void normalizeDocument()
 * @method Node querySelector($selector)
 * @method NodeList querySelectorAll($selector)
 * @method Node removeChild(Node $oldNode)
 * @method Node replaceChild(Node $newNode, Node $oldNode)
 * @method bool validate()
 */
class Document extends ResponseContent {

const DEFAULT_HTML = "<!doctype html>";
public $contentType = "text/html";
public $domDocument;
public $node;
public $nodeMap = [];

public $head;
public $body;

/**
 * Passing in the HTML to parse as an optional first parameter automatically
 * calls the load function with provided HTML content.
 *
 * @param string|DOMDocument $source Raw HTML string, or existing native
 * DOMDocument to represent
 * @param ConfigObj $config Response configuration object
 */
public function __construct($source = null, $config = null) {
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

	$this->config = $config;

	if(!isset($this->node)) {
		$this->node = new Node($this, $this->domDocument);
	}

	// Store a self-reference in the native DOMDocument, for access within the
	// Node class.
	$this->domDocument->document = $this;
	$uuid = uniqid("nodeMap-", true);
	$this->domDocument->uuid = $uuid;
	$this->domDocument->encoding = "utf-8";

	// Find and reference the DOCTYPE element:
	foreach ($this->childNodes as $rootChild) {
		if($rootChild->nodeType === XML_DOCUMENT_TYPE_NODE) {
			$rootChild->description = "DOCTYPE NODE";
		}
	}

	$htmlNodeList = $this->getElementsByTagName("html");
	if(count($htmlNodeList) === 0) {
		$htmlNode = $this->document->createElement("html");
		$this->document->appendChild($htmlNode);
	}
	else {
		$htmlNode = $htmlNodeList[0];
	}
	$htmlNode = Node::wrapNative($htmlNode);

	// Check the head and body tags exist in the document, no matter what.
	$requiredRootTagArray = ["head", "body"];
	foreach ($requiredRootTagArray as $tag) {
		$nodeList = $this->domDocument->getElementsByTagName($tag);
		if($nodeList->length > 0) {
			$this->$tag = Node::wrapNative($nodeList->item(0));
		}
		else {
			$this->$tag = $this->createElement($tag);
			$htmlNode->insertBefore(
				$this->$tag, $htmlNode->firstChild);
		}
	}

	$this->tidy();
}

public function createElement($node,
$attributeArray = [], $nodeValue = null) {
	return new Node($this, $node, $attributeArray, $nodeValue);
}

/**
 *
 */
public function createManifest($request, $response) {
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

	$this->domDocument->loadHTML(mb_convert_encoding(
			$string, "HTML-ENTITIES", "UTF-8")
	);
}

/**
 * Tidies up elements within the body and puts them in the head.
 * For example, meta tags and title tags should be put into the head, but may
 * be useful to declare them in the body.
 */
public function tidy() {
	$tagArray = [
		"meta" => "name",
		"link" => "rel",
		"title" => null,
	];

	foreach ($tagArray as $tag => $attr) {
		$selector = $tag;
		$insertBeforeNode = null;
		if(!is_null($attr)) {
			$selector .= "[$attr]";
		}

		// Skip if no body assigned.
		if(!$this->body instanceof Node) {
			continue;
		}

		foreach($this->body->querySelectorAll($selector) as $element) {
			// Remove existing nodes that have the same value for
			// their given attribute.
			// Skip if there is no head assigned.
			if(!$this->head instanceof Node) {
				continue;
			}
			foreach($this->head->querySelectorAll($selector) as $headElement) {
				if($headElement->getAttribute($attr)
				!== $element->getAttribute($attr)) {
					continue;
				}

				$insertBeforeNode = $headElement->nextSibling;
				$headElement->remove();
			}

			$this->head->insertBefore($element, $insertBeforeNode);
		}
	}
}

/**
 *
 */
public function __toString() {
	return $this->domDocument->saveHTML();
}

/**
 *
 */
public function __call($name, $args) {
	// TODO: Throw exception on appendChild related stuff.
	// See what JavaScript does: document.appendChild(something)
	// "HierarchyRequestError: Failed to execute 'appendChild' on 'Node': Nodes
	// of type 'DIV' may not be inserted inside nodes of type '#document'."
	$result = call_user_func_array([$this->node, $name], $args);
	return $result;
}

public function __get($name) {
	if(property_exists($this->domDocument, $name)) {
		$value = Node::wrapNative($this->domDocument->$name);
	}
	else {
		throw new InvalidNodePropertyException($name);
	}

	return $value;
}

}#
