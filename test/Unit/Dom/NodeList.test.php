<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

class NodeList_Test extends \PHPUnit_Framework_TestCase {

private $document;

public function setUp() {
	$this->document = new Document("<!doctype html>"
	. "<h1>Test heading</h1>"
	. "<p id='para1'>Test paragraph one</p>"
	. "<p id='para2'>Test paragraph two</p>");
}

public function tearDown() {}

public function testConstructsWithArray() {
	$nodeList = new NodeList([
		$this->document->getElementById("para1"),
		$this->document->getElementById("para2"),
	]);

	$this->assertInstanceOf("\Gt\Dom\NodeList", $nodeList);
}

public function testConstructsWithNodeList() {
	$nodeList = new NodeList([
		$this->document->getElementById("para1"),
		$this->document->getElementById("para2"),
	]);

	$anotherNodeList = new NodeList($nodeList);

	$this->assertInstanceOf("\Gt\Dom\NodeList", $anotherNodeList);
}

public function testConstructsWithDomNodeList() {
	$domNodeList = $this->document->domDocument->getElementsByTagName("p");
	$this->assertInstanceOf("\DOMNodeList", $domNodeList);

	$nodeList = new NodeList($domNodeList);
	$this->assertInstanceOf("\Gt\Dom\NodeList", $nodeList);
}

/**
 * @expectedException \Gt\Dom\InvalidNodeTypeException
 */
public function testDoesNotConstructWithInvalidParameter() {
	$nodeList = new NodeList(7 * 7 + 7 * 7 + 7);
}

public function testCount() {
	$nodeList = $this->document->getElementsByTagName("p");
	$this->assertCount(2, $nodeList);
}

public function testForEach() {
	$nodeList = $this->document->getElementsByTagName("p");
	$previousNode = null;

	foreach ($nodeList as $i => $node) {
		$this->assertNotSame($previousNode, $node);
		$this->assertSame($nodeList[$i], $node);
		$previousNode = $node;
	}
}

public function testArrayAccess() {
	$nodeList = $this->document->getElementsByTagName("p");
	$this->assertArrayHasKey(0, $nodeList);
	$this->assertArrayHasKey(1, $nodeList);
	$this->assertArrayNotHasKey(2, $nodeList);
	$this->assertArrayNotHasKey("test", $nodeList);

	$nodeList[2] = $this->document->createElement("button");
	$this->assertArrayHasKey(2, $nodeList);

	unset($nodeList[2]);
	$this->assertArrayNotHasKey(2, $nodeList);
}

/**
 * @expectedException \Gt\Dom\InvalidNodeTypeException
 */
public function testArrayAccessThrowsExceptionFromInvalidType() {
	$nodeList = $this->document->getElementsByTagName("p");
	$nodeList[9] = new \DateTime();
}

}#