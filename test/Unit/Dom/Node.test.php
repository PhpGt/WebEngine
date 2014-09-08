<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
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

}#