<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

class Node_Test extends \PHPUnit_Framework_TestCase {

private $document;

public function setUp() {
	$this->document = new Document("<!doctype html>"
	. "<h1>Test heading</h1>"
	. "<p>Test paragraph one</p>"
	. "<p>Test paragraph two</p>");
}

public function tearDown() {}

public function testNodeConstructsWithString() {
	$text = "Test text content";

	$node = new Node($this->document, "span");
	$node->textContent = $text;

	$this->assertEquals($text, $node->textContent);
}

public function testNodeConstructsWithNativeDomNode() {
	$domNode = $this->document->domDocument->createElement("span");
	$this->assertInstanceOf("\DOMNode", $domNode);
	$anotherDomNode = $this->document->domDocument->createElement("span");

	$node = new Node($this->document, $domNode);
	$this->assertSame($domNode, $node->domNode);
	$this->assertNotSame($anotherDomNode, $node->domNode);
}

public function testNodeConstructsWithExtensionNode() {
	$document = new Document();
	$spanNode = $document->createElement("span");
	$spanNode->textContent = "Test text content";

	$anotherSpanNode = new Node($document, $spanNode);

	$this->assertNotSame($anotherSpanNode, $spanNode);
	$this->assertEquals($anotherSpanNode->textContent, $spanNode->textContent);
}

/**
 * @expectedException \Gt\Core\Exception\InvalidArgumentTypeException
 */
public function testNodeDoesNotConstructWithInvalidType() {
	new Node(null, new \StdClass());
}

public function testNodeConstructorSetsDomNodeAttributes() {
	$attributeArray = [
		"id"		=> "elementId",
		"name"		=> "elementName",
		"value"		=> "elementValue",
		"data-test"	=> "elementDataAttribute",
	];

	$node = new Node($this->document, "div", $attributeArray);
	$domNode = $node->domNode;

	foreach ($attributeArray as $key => $value) {
		$this->assertEquals($value, $domNode->getAttribute($key));
	}
}

public function testNodeConstructorSetsDomNodeValue() {
	$tagValueMap = [
		"p" 		=> "Paragraph value",
		"button" 	=> "Button value",
		"input" 	=> "Input value",
	];

	$this->document = new Document();

	foreach ($tagValueMap as $key => $value) {
		$node = new Node($this->document, $key, [], $value);
		$this->assertEquals($value, $node->value);
	}
}

public function testShorthandPropertyGetsAndSetsAttributes() {
	$node = $this->document->createElement("p");
	$test = $node->aMadeUpProperty;

	$this->assertNull($test, 'should not have aMadeUpProperty yet');

	$node->aMadeUpProperty = "testValue";

	$this->assertEquals("testValue", $node->getAttribute("aMadeUpProperty"));
}

public function testCallNativeMethod() {
	$node = $this->document->createElement("div");
	$this->assertFalse($node->hasChildNodes());

	$this->assertFalse($node->isSameNode($this->document));
}

public function testCallNativeMethodWrapsWithExtendedNode() {
	$node = $this->document->createElement("div");
	$paragraphNode = $this->document->createElement("p");

	$node->appendChild($paragraphNode);

	$paragraphNodeList = $node->getElementsByTagName("p");

	$this->assertInstanceOf("\Gt\Dom\NodeList", $paragraphNodeList);
}

/**
 * @expectedException \Gt\Dom\NodeMethodNotDefinedException
 */
public function testCallInvalidNativeMethod() {
	$node = $this->document->createElement("div");
	$node->callToUndefinedMethod("One", "Two", "Three");
}

const VALUETYPE_ATTRIBUTE			= "valuetype_attribute";
const VALUETYPE_SUBCHILDSELECTED	= "valuetype_subchildselected";
const VALUETYPE_CONTENT				= "valuetype_content";

private $data_nodeValueMap = [
	self::VALUETYPE_ATTRIBUTE => [
		"button", "input", "command", "embed", "object", "script",
		"source", "style", "menu", "option",
	],
	self::VALUETYPE_SUBCHILDSELECTED => [
		"select",
	],
	self::VALUETYPE_CONTENT => [
		"div", "p", "span", "article", "section", "blockquote",
	],
];
public function data_nodeValueMap() {
	$return = [];

	foreach ($this->data_nodeValueMap as $valueType => $tagList) {
		foreach ($tagList as $tag) {
			$value = uniqid();
			$return []= [$tag, $valueType, $value];
		}
	}

	return $return;
}

/**
 * @dataProvider data_nodeValueMap
 */
public function testSetValueAttribute($tag, $valueType, $value) {
	$node = $this->document->createElement($tag);

	if(strtolower($tag) === "select") {
		// Create some random options, including one with the value of $value.
		for($i = 0; $i < 10; $i++) {
			$option = $this->document->createElement("option");
			$option->value = uniqid();
			$option->textContent = "Option element $i";
			$node->appendChild($option);
		}

		$optionNodeList = $node->querySelectorAll("option");
		$randomPosition = rand(0, count($optionNodeList) - 1);

		$option = $this->document->createElement("option");
		$option->value = $value;
		$node->insertBefore($option, $optionNodeList[$randomPosition]);
	}

	$node->value = $value;

	switch($valueType) {
	case self::VALUETYPE_ATTRIBUTE:
		$this->assertEquals($value, $node->getAttribute("value"));
		break;

	case self::VALUETYPE_SUBCHILDSELECTED:
		$checkValue = null;

		foreach ($node->childNodes as $option) {
			if($option->hasAttribute("selected")) {
				$checkValue = $option->getAttribute("value");
			}
		}
		$this->assertEquals($checkValue, $value);
		break;

	case self::VALUETYPE_CONTENT:
		$this->assertEquals($value, $node->textContent);
		break;
	}
}

/**
 * @dataProvider data_nodeValueMap
 */
public function testGetValueSelect($tag, $valueType, $value) {
	if(strtolower($tag) !== "select") {
		return;
	}

	$node = $this->document->createElement($tag);

	// Create some random options, including one with the value of $value.
	for($i = 0; $i < 10; $i++) {
		$option = $this->document->createElement("option");
		$option->value = "option-$i";
		$option->textContent = "Option element $i";
		$node->appendChild($option);
	}

	$optionNodeList = $node->querySelectorAll("option");
	$randomPosition = rand(0, count($optionNodeList) - 1);

	$optionNodeList[$randomPosition]->setAttribute("selected", "");

	$value = $node->value;

	$checkValue = null;

	foreach ($node->childNodes as $option) {
		if($option->hasAttribute("selected")) {
			$checkValue = $option->getAttribute("value");
		}
	}
	$this->assertEquals($checkValue, $value);
}


public function data_dateTime() {
	$return = [];

	$dateTimeMin = new \DateTime("1988-05-04");
	$dateTimeMax = new \DateTime("2093-06-02");
	$timeZoneArray = \DateTimeZone::listIdentifiers();

	for($i = 0; $i < 100; $i++) {
		$timestampRandom = rand(
			$dateTimeMin->getTimestamp(),
			$dateTimeMax->getTimestamp()
		);
		$dateTime = new \DateTime();
		$timeZoneRandom = new \DateTimeZone(
			$timeZoneArray[array_rand($timeZoneArray)]
		);

		$dateTime->setTimeZone($timeZoneRandom);
		$dateTime->setTimestamp($timestampRandom);
		$return []= [$dateTime];
	}

	return $return;
}
/**
 * @dataProvider data_dateTime
 */
public function testSetValueDateTime(\DateTime $dateTime) {
	$node = $this->document->createElement("input");
	$node->setAttribute("type", "date");

	$node->value = $dateTime;

	$this->assertEquals($dateTime->format(\DateTime::RFC3339), $node->value);
}

public function testWrapNative() {
	$document = Node::wrapNative($this->document->domDocument);
	$this->assertSame($document, $this->document);

	$node = $document->createElement("span");
	$wrappedNode = Node::wrapNative($node->domNode);
	$this->assertSame($node, $wrappedNode);

	$nodeList = $document->getElementsByTagName("span");
	$wrappedNodeList = Node::wrapNative($nodeList);
	$this->assertInstanceOf("\Gt\Dom\NodeList", $wrappedNodeList);
}

public function testQuerySelector() {
	$parentNode = $this->document->createElement("div");
	$parentNode->id = "parent";
	// Uncle isn't a parent or sibling of the child, but does have common
	// ancestry with the grandparent (document).
	$uncleNode = $this->document->createElement("div");
	$uncleNode->id = "uncle";
	$childNode = $this->document->createElement("div");
	$childNode->id = "child";

	// Append the three elements to where they should exist in the document.
	$this->document->body->appendChild($parentNode);
	$this->document->body->appendChild($uncleNode);
	$parentNode->appendChild($childNode);

	// Perform getElementById, to check the element actually exists.
	$selectedDomNode = $this->document->domDocument->getElementById("child");
	$this->assertSame($selectedDomNode, $childNode->domNode);
	// Perform native DOMXpath query, to check everything's in order.
	$xpath = new \DOMXPath($this->document->domDocument);
	$domNodeList = $xpath->query(".//*[@id='child']",
		$this->document->node->domNode);
	$selectedChildDomNode = $domNodeList->item(0);
	$this->assertSame($selectedChildDomNode, $childNode->domNode);

	$nodeList = $this->document->xpath(".//*[@id='child']");
	$selectedChildNode = $nodeList[0];
	$this->assertSame($selectedChildNode, $childNode);

	$selectedChildNode = $this->document->querySelector("#child");
	$this->assertSame($selectedChildNode, $childNode);

	// Uncle should not contain any divs, but is a div himself.
	$shouldNotMatch = $uncleNode->querySelector("div");
	$this->assertNull($shouldNotMatch);
}

public function testCheckContext() {
	$node = $this->document->createElement("div");

	$context = $node->checkContext(null);
	$this->assertInstanceOf("\DOMNode", $context);

	$context = $node->checkContext($this->document);
	$this->assertInstanceOf("\DOMNode", $context);
}

/**
 * @expectedException \Gt\Dom\InvalidNodeTypeException
 */
public function testCheckInvalidContext() {
	$node = $this->document->createElement("div");

	$context = $node->checkContext("invalid parameter type");
}

// public function testGetSetDomProperties() {
// 	$node = $this->document->createElement("div");
// 	$this->document->documentElement->appendChild($node);

// 	$node->id = "div-id";
// 	$this->assertEquals("div-id", $node->id);
// 	$this->assertEquals("div-id", $node->getAttribute("id"));
// 	$this->assertSame($node, $this->document->getElementById("div-id"));

// 	$node->className = "oneClass";
// 	$this->assertEquals("oneClass", $node->className);
// 	$this->assertEquals("oneClass", $node->getAttribute("class"));

// 	$node->className = "oneClass twoClass";
// 	$this->assertEquals("oneClass twoClass", $node->className);
// 	$this->assertEquals("oneClass twoClass", $node->getAttribute("class"));

// 	$this->assertSame($node,
// 		$this->document->getElementsByClassName("oneClass")[0]);
// 	$this->assertSame($node,
// 		$this->document->getElementsByClassName("twoClass")[0]);
// }

}#