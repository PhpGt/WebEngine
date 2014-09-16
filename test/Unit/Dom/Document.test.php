<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

class Document_Test extends \PHPUnit_Framework_TestCase {

public function testDocumentConstructsWithNoParameters() {
	$document = new Document();
	$this->assertInstanceOf("\Gt\Response\ResponseContent", $document);
}

public function testDocumentConstructsWithWrongParameters() {
	$emptyString = "";
	$document = new Document($emptyString);

	$defaultString = Document::DEFAULT_HTML;
	$defaultDocument = new Document($defaultString);

	$this->assertEquals((string)$document, (string)$defaultDocument);
}

public function testLoadHtml() {
	$html = "<!doctype html><p>This is just a test</p>";
	$document = new Document($html);
	$this->assertEquals("This is just a test", $document->textContent);

	$html = "<!doctype html><p>Another test, loaded</p>";
	$document->load($html);
	$this->assertEquals("Another test, loaded", $document->textContent);
}

public function testLoadHtmlDefault() {
	$html = null;
	$document = new Document();
	$document->load($html);

	$this->assertEmpty($document->textContent);
}

public function testCreatesHead() {
	// $document = new Document();
	// $document->xpath(".//head");
}

public function testCreatesBody() {

}

public function testDocumentCallsNodeMethods() {
	// Should be able to call Node and DOMNode functions on the Document.
	$document = new Document("<!doctype html><h1>Hello, Test</h1>");
	$h1Node = $document->querySelector("h1");
	$this->assertInstanceOf("\Gt\Dom\Node", $h1Node);
	$this->assertEquals("H1", $h1Node->tagName);
}

/**
 * @expectedException \Gt\Dom\NodeMethodNotDefinedException
 */
public function testDocumentCallToInvalidNodeMethod() {
	$document = new Document();
	$document->binky();
}

/**
 * @expectedException \Gt\Dom\InvalidNodePropertyException
 */
public function testDocumentGetInvalidProperty() {
	$document = new Document();
	$binky = $document->binky;
}

public function testGetNodeWrapsNativeDomNode() {
	$document = new Document("<!doctype html><h1>Hello, Test</h1>");
	$domDocument = $document->domDocument;

	// Use native DOMDocument to get reference to h1.
	$domNodeList = $domDocument->getElementsByTagName("h1");
	$h1DomNode = $domNodeList->item(0);
	$ownerDocument = $h1DomNode->ownerDocument;

	// Make sure that interfacing a native element isn't overridden at all.
	$this->assertInstanceOf("\DOMDocument", $ownerDocument);

	// Make sure that known properties are NOT wrapped with Gt\Dom extensions.
	$this->assertInstanceOf("\DOMDocument", $domDocument);

	// At first, there should be no UUID attribute on the native DOMNode.
	$this->assertObjectNotHasAttribute("uuid", $h1DomNode);

	$node = $document->getNode($h1DomNode);
	$this->assertInstanceOf("\Gt\Dom\Node", $node);
	$this->assertSame($h1DomNode, $node->domNode);
	$this->assertObjectHasAttribute("uuid", $h1DomNode);

	// Perform getNode on an already-extended Node object..
	// .. should return itself.
	$this->assertSame($node, $document->getNode($node));

	// Get node on native DOMDocument for the first time.
	$this->assertSame($document, $document->getNode($domDocument));
	// Mess up the uuid map value.
	$domDocument->uuid = "edited directly from test!";
	// Should still get reference back to same document.
	$this->assertSame($document, $document->getNode($domDocument));
}

/**
 * @expectedException \Gt\Dom\InvalidNodeTypeException
 */
public function testGetNodeThrowsException() {
	$document = new Document();
	$document->getNode("Strings are not valid!");
}

public function testDocumentConstructedWithDomDocumentSource() {
	$domDocument = new \DOMDocument("1.0", "utf-8");
	$html = "<!doctype html><h1>Created using native DOMDocument!</h1>";
	$domDocument->loadHtml($html);

	$document = new Document($domDocument);

	$this->assertSame($domDocument, $document->domDocument);
}

}#