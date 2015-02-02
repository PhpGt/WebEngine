<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
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

public function testDocumentTidy() {
	$html = "<!doctype html><body><h1>Test</h1><meta name=\"test\" /></body>";
	$document = new Document($html);

	$meta = $document->querySelector("meta[name='test']");
	$this->assertNotNull($meta);

	$this->assertSame($meta->parentNode, $document->head,
		"Meta should be moved into the head");

	$html = "<!doctype html><title>Old title</title>"
		."<h1>Test</h1><title>New title</title>";
	$document = new Document($html);

	$titleList = $document->querySelectorAll("title");
	$this->assertCount(1, $titleList, "Should only be one title");
	$this->assertSame($titleList[0]->parentNode, $document->head);

	// A more complex, multi-element example:
	$html = "<!doctyle html>
		<head>
			<meta charset='utf-8' />
			<title>The old title</title>
			<link rel='next' href='/incorrect-next' />
			<link rel='prev' href='prev-page' />
		</head>
		<body>
			<h1>Correct title</h1>
			<title>Correct title</title>
			<p>Some content</p>
			<a href='/next'>Next</a>
			<link rel='next' href='next-page' />
		</body>";
	$document = new Document($html);

	$title = $document->querySelector("title");
	$linkList = $document->querySelectorAll("link");
	$this->assertCount(2, $linkList, 'There should be two links in total');

	$this->assertEquals("Correct title", $title->textContent);

	// This tests the order of the inserted node too:
	$this->assertEquals("next-page", $linkList[0]->getAttribute("href"));
	$this->assertEquals("prev-page", $linkList[1]->getAttribute("href"));
}

}#