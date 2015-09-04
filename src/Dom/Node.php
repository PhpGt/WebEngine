<?php
/**
 * This class is an extension to the native DOMElement present in PHP, aiming
 * to provide a DOM-level-4-capable interface by defining missing methods and
 * properties.
 *
 * A Node is an interface from which a number of DOM types inherit, and allows
 * these various types to be treated (or tested) similarly. It represents an
 * object within a DOM Document. This class describes methods and properties
 * common to all kinds of Node elements.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

use Symfony\Component\CssSelector\CssSelector;

/**
 * @property string $nodeValue
 * @property string $value
 * @property string $textContent
 * @property-read Node $parentNode
 * @property-read NodeList $childNodes
 * @property-read Node $firstChild
 * @property-read Node $lastChild
 * @property-read Node $previousSibling
 * @property-read Node $previousElementSibling
 * @property-read Node $nextSibling
 * @property-read Node $nextElementSibling
 * @property-read DOMNamedNodeMap $attributes
 * @property-read Document $ownerDocument
 * @property Document $document
 *
 * @method Node appendChild(Node $newNode)
 * @method Node cloneNode(bool $deep)
 * @method string getAttribute(string name)
 * @method Node getElementById(string $ID)
* @method NodeList getElementsByClassName(string $name)
* @method NodeList getElementsByName(string $name)
 * @method NodeList getElementsByTagName(string $name)
 * @method int getLineNo()
 * @method string getNodePath()
 * @method bool hasAttribute(string $name)
 * @method bool hasAttributes()
 * @method bool hasChildNodes()
 * @method Node insertBefore(Node $newNode, Node $refNode)
 * @method bool isSameNode(Node $node)
 * @method void normalize()
 * @method bool removeAttribute(string $name)
 * @method Node removeChild(Node $oldNode)
 * @method Node replaceChild(Node $newNode, Node $oldNode)
 * @method DOMAttr setAttribute(string $name, string $value)
 *
 */
class Node {

public $domNode;
public $tagName;
public $classList;

public static $textReplacementArray = [
	"&period;" => ".",
];

/**
 *
 */
public function __construct($document, $node,
array $attributeArray = array(), $nodeValue = null) {
	if($node instanceof Node) {
		$this->domNode = $node->domNode;
	}
	else if($node instanceof \DOMNode) {
		$this->domNode = $node;
	}
	else if(is_string($node)) {
		$domDocument = $document->domDocument;
		$this->domNode = $domDocument->createElement($node);
	}
	else {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException();
	}

	foreach ($attributeArray as $key => $value) {
		$this->setAttribute($key, $value);
	}

	if($this->domNode instanceof \DOMElement) {
		// Fix case, according to W3 spec
		// http://www.w3.org/TR/REC-DOM-Level-1/level-one-core.html#ID-745549614
		$this->tagName = strtoupper($this->domNode->tagName);

		// Add classList property (only for Elements)
		$this->classList = new TokenList($this, "class");
	}

	if(!is_null($nodeValue)) {
		$this->value = $nodeValue;
	}

	// Attach a UUID to the underlying DOMNode and store a reference in the
	// Document::nodeMap. This allows for accessing already-constructed Node
	// objects through Document::getNode(), rather than constructing again.
	if(!property_exists($this->domNode, "uuid")) {
		$uuid = uniqid("nodeMap-", true);
		$this->domNode->uuid = $uuid;
		$this->domNode->ownerDocument->document->nodeMap[$uuid] = $this;
	}
}

/**
 *
 */
public function __get($name) {
	switch($name) {
	case "id":
	case "ID":
		$value = $this->getAttribute("id");
		break;

	case "className":
		$value = $this->getAttribute("class");
		break;

	case "value":
		$value = $this->getValue();
		break;

	case "innerHTML":
	case "innerHtml":
	case "html":
	case "HTML":
		$innerHTML = "";
		$children = $this->childNodes;

		foreach ($children as $child) {
			$innerHTML .= $child->ownerDocument->saveHTML($child);
		}

		return html_entity_decode($innerHTML);
		break;

	case "textContent":
		$textContent = $this->domNode->textContent;
		$textContent = mb_convert_encoding(
			$textContent,
			"HTML-ENTITIES",
			"UTF-8"
		);
//		foreach(self::$textReplacementArray as $search => $replace) {
//			$textContent = str_replace($search, $replace, $textContent);
//		}

		return html_entity_decode($textContent, ENT_HTML5);
		break;

	case "previousElementSibling":
		$sibling = $this;

		while(!is_null($sibling)
		&& !$sibling->domNode instanceof \DOMElement) {
			$sibling = $sibling->previousSibling;
		}

		return $sibling;
		break;

	case "nextElementSibling":
		$sibling = $this;

		while(!is_null($sibling)
			&& !$sibling->domNode instanceof \DOMElement) {
			$sibling = $sibling->nextSibling;
		}

		return $sibling;
		break;

	default:
		if(property_exists($this->domNode, $name)) {
			$value = $this->domNode->$name;
			$value = self::wrapNative($value);
		}
		else {
			if($this->hasAttribute($name)) {
				$value = $this->getAttribute($name);
			}
			else {
				$value = null;
			}
		}
		break;
	}

	return $value;
}

/**
 *
 */
public function __set($name, $value) {
	switch($name) {
	case "id":
	case "ID":
		$this->setAttribute("id", $value);
		break;

	case "className":
		$this->setAttribute("class", $value);
		break;

	case "description":
		$this->description = $value;
		break;

	case "textContent":
	case "innerText":
	case "text":
		$value = htmlentities($value, ENT_HTML5);
		$this->domNode->nodeValue = $value;
		break;

	case "innerHTML":
	case "innerHtml":
	case "html":
	case "HTML":
		$value = mb_convert_encoding($value, "html-entities", "utf-8");

		// If plain text string is provided, skip generating new Document.
		if($value === strip_tags($value)) {
			$this->textContent = $value;
			break;
		}

		// Remove all content from current node first.
		$this->textContent = "";

		$tempDoc = new Document($value);
		foreach($tempDoc->body->childNodes as $child) {
			$importedChild = $this->ownerDocument->importNode($child, true);
			$this->appendChild($importedChild);
		}

		break;

	case "value":
		$this->setValue($value);
		break;

	case "templateName":
	case "templateParentNode":
	case "templatePreviousSibling":
	case "templateNextSibling":
		$this->domNode->$name = $value;
		break;

	default:
		$this->setAttribute($name, $value);
	}
}

/**
 *
 */
public function __call($name, $args) {
	if(method_exists($this->domNode, $name)) {
		// Convert each argument to native DOMDocument implementation where
		// possible.
		foreach ($args as $i => $arg) {
			if($arg instanceof Document) {
				$args[$i] = $arg->domDocument;
			}
			else if($arg instanceof Node) {
				$args[$i] = $arg->domNode;
			}
		}

		$value = call_user_func_array([$this->domNode, $name], $args);

		// Attempt to never pass back a native DOMNode, wrapping it in
		// a Node class instead.
		$value = self::wrapNative($value);
		return $value;
	}

	if($name === "insertTemplate"
	&& !empty($this->domNode->templateName)) {
		$this->templateParentNode->insertBefore(
			$this,
			$this->templateNextSibling
		);
		return;
	}

	throw new NodeMethodNotDefinedException($name);
}

/**
 * Removes the node from the tree it belongs to.
 */
public function remove() {
	return $this->parentNode->removeChild($this);
}

/**
 * Gets the node value according to the node type. Typical block element'
 * value represents its textContent, however certain elements can have a value
 * attribute (such as input elements).
 *
 * @return string The node value as a string
 */
private function getValue() {
	$value = null;

	switch($this->tagName) {
	// The following tags can accept a value attribute:
	// developer.mozilla.org/en-US/docs/Web/HTML/Attributes
	case "BUTTON":
	case "INPUT":
	case "COMMAND":
	case "EMBED":
	case "OBJECT":
	case "SCRIPT":
	case "SOURCE":
	case "STYLE":
	case "MENU":
	case "OPTION":
		$value = $this->getAttribute("value");
		if(empty($value)) {
			$value = $this->textContent;
		}
		break;

	// Loop through a select's option elements and set the 'selected' attribute
	// on the option whose own value matches the current $value.
	// dev.w3.org/html5/spec-preview/common-microsyntaxes#boolean-attributes
	case "SELECT":
		$optionList = $this->querySelectorAll("option");
		for($i = 0, $len = count($optionList); $i < $len; $i++) {
			if($optionList[$i]->hasAttribute("selected")) {
				$value = $optionList[$i]->getAttribute("value");
			}
		}
		break;

	default:
		$value = $this->domNode->nodeValue;
		break;
	}

	return $value;
}

/**
 * Sets the node value according to the node type. Typical block elements'
 * value represents its textContent, however certain elements have a value
 * attribute (such as input elements), some elements can accept objects as
 * values (such as date elements accepting DateTime objects), and some elements
 * have children that need modifying due to element value (such as select
 * elements' options being selected).
 *
 * @param string|object $value The value to set
 */
private function setValue($value) {
	if($value instanceof \DateTime) {
		// w3.org/TR/html-markup/input.datetime.html#input.datetime.attrs.value
		$value = $value->format(\DateTime::RFC3339);
	}

	switch($this->tagName) {
	// The following tags can accept a value attribute:
	// developer.mozilla.org/en-US/docs/Web/HTML/Attributes
	case "BUTTON":
	case "INPUT":
	case "COMMAND":
	case "EMBED":
	case "OBJECT":
	case "SCRIPT":
	case "SOURCE":
	case "STYLE":
	case "MENU":
	case "OPTION":
		$this->setAttribute("value", $value);
		break;

	// Loop through a select's option elements and set the 'selected' attribute
	// on the option whose own value matches the current $value.
	// dev.w3.org/html5/spec-preview/common-microsyntaxes#boolean-attributes
	case "SELECT":
		$optionList = $this->querySelectorAll("option");
		for($i = 0, $len = count($optionList); $i < $len; $i++) {
			if($optionList[$i]->value == $value) {
				$optionList[$i]->setAttribute("selected", "");
			}
		}
		break;

	default:
		$value = htmlentities($value);
		$this->domNode->nodeValue = $value;
		break;
	}
}

/**
 * Attempt to never pass back a native DOMNode, wrapping it in the appropriate
 * Gt\Dom extension class.
 *
 * @param DOMDocument|DOMNode|DOMNodeList $node Native object to wrap in
 * extension class
 *
 * @return Document|Node|NodeList Instance of Gt\Dom extension class
 */
public static function wrapNative($node) {
	if($node instanceof \DOMDocument) {
		$node = $node->document->getNode($node);
	}
	else if($node instanceof \DOMNode) {
		$node = $node->ownerDocument->document->getNode($node);
	}
	else if($node instanceof \DOMNodeList) {
		$node = new NodeList($node);
	}

	return $node;
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
	$nodeList = $this->css($query, $this);
	if(count($nodeList) > 0) {
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
	return $this->css($query, $this);
}

/**
 *
 */
public function css($query, $context = null) {
	$context = $this->checkContext($context);
	// Second parameter of toXPath is optional query prefix.
	$xpath = CssSelector::toXPath($query, ".//");
	$domNodeList = $this->xpath($xpath, $context);
	return new NodeList($domNodeList);
}

/**
 *
 */
public function xpath($query, $context = null) {
	if(is_null($context)) {
		$context = $this;
	}
	$context = $this->checkContext($context);

	$domDocument = $this->ownerDocument;
	if(is_null($domDocument)) {
		$domDocument = $this->domNode;

		if(isset($domDocument->ownerDocument)) {
			$domDocument = $domDocument->ownerDocument;
		}
	}

	if($domDocument instanceof Document) {
		$domDocument = $domDocument->domDocument;
	}

	$xpath = new \DOMXPath($domDocument);
	$domNodeList = $xpath->query($query, $context);

	return new NodeList($domNodeList);
}

/**
 * Ensures the provided context is of a native DOMDocument type rather than
 * an enhanced object, so it can be used with native DOMDocument methods.
 *
 * @param Node|\DOMNode|null $context The current context. If null is provided,
 * this current Node's DOMNode is used.
 *
 * @return \DOMNode The contextual DOMNode
 */
public function checkContext($context) {
	if(is_null($context)) {
		if($this->getNodePath() === "/") {
			// This is a document.
			$context = $this->document->documentElement;
		}
		else {
			$context = $this->domNode;
		}
	}

	if($context instanceof Document) {
		$context = $context->documentElement;
	}

	if($context instanceof Node) {
		$context = $context->domNode;
	}
	else if(!$context instanceof \DOMNode) {
		throw new InvalidNodeTypeException(gettype($context));
	}

	return $context;
}

/**
 * Returns NodeList containing all child elements which have all of the given
 * class names. When called on the document object, the complete document is
 * searched, including the root node. You may also call getElementsByClassName
 * on any element; it will return only elements which are descendants of the
 * specified root element with the given class names.
 *
 * @param string $classNames Space-separated class list to search against
 *
 * @return NodeList NodeList containing all matching child elements
 */
public function getElementsByClassName($classNames) {
	$query = str_replace(" ", ".", $classNames);
	$query = "." . $query;

	return $this->querySelectorAll($query);
}

}#
