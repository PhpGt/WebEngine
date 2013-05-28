<?php class DomTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", getcwd() . "/");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "Framework/Component/DomEl.php");
	require_once(GTROOT . "Framework/Component/DomElCollection.php");
}

public function testDomIsDom() {
	$dom = new Dom();
	$dom2 = new Dom();
	$this->assertTrue(gettype($dom) === gettype($dom2));
}

}#