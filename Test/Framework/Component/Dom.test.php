<?php class DomTest extends PHPUnit_Framework_TestCase {

public function setup() {
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Class/Log/Log.class.php");
	require_once(GTROOT . "/Class/Log/Logger.class.php");
}

public function testDomElInDom() {
	$dom = new Dom();
	$el = $dom->create("div");

	$domDoc = $dom->getDomDoc();
	$ownerOfEl = $el->ownerDocument;
	
	$this->assertEquals($domDoc, $ownerOfEl);
	$this->assertTrue(get_class($domDoc) === get_class($ownerOfEl));
}

/**
 * Tests that when a DomEl has a native DOM method called on it, if DomEl or 
 * DomElCollections are passed as arguments to the method, they need converting
 * to Node or NodeList objects, otherwise DOMDocument will throw a type error.
 */
public function testDomElNodeArgumentsAreConvertedToNodes() {
	$dom = new Dom();
	$parent = $dom->createElement("div");
	$child1 = $dom->createElement("p");
	$child1->setAttribute("id", "child1");
	$child2 = $dom->createElement("p");
	$child2->setAttribute("id", "child2");

	$parent->appendChild($child1);

	// Check that the method we are testing is not overridden from default DOM.
	$this->assertFalse(method_exists($parent, "insertBefore"));

	$parent->insertBefore($child2, $child1);
	$paragraphs = $parent["p"];

	$this->assertEquals("child2", $paragraphs[0]->id);
	$this->assertEquals("child1", $paragraphs[1]->id);
}

/**
 * The $dom object can be used to xpath or css select inner dom nodes, but what
 * if it is misused?
 * $dom[""] or $dom[123] for example.
 */
public function testDomSelectorErrors() {
	$dom = new Dom();
	$caughtException = false;
	try {
		$nothing = $dom[""];		
	}
	catch(Exception $e) {
		$caughtException = true;
	}

	$this->assertTrue($caughtException);
}

}#