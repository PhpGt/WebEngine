<?php class DomTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", getcwd() . "/");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "Framework/Component/DomEl.php");
	require_once(GTROOT . "Framework/Component/DomElCollection.php");
	require_once(GTROOT . "Framework/Component/DomElClassList.php");
}

public function testDomElInDom() {
	$dom = new Dom();
	$el = $dom->create("div");

	$domDoc = $dom->getDomDoc();
	$ownerOfEl = $el->ownerDocument;
	
	$this->assertEquals($domDoc, $ownerOfEl);
	$this->assertTrue(get_class($domDoc) === get_class($ownerOfEl));
}

}#