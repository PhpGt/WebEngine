<?php 
namespace Framework\Component;
class DomTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", "/home/g105b/Webroot/PHP.Gt/");
	define("DS", DIRECTORY_SEPARATOR);
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "Framework/Component/DomEl.cmp.php");
	require_once(GTROOT . "Framework/Component/DomElCollection.cmp.php");
}

public function testDomIsDom() {
	$dom = new Dom();
	$dom2 = new Dom();
	$this->assertTrue(gettype($dom) === gettype($dom2));
}

}?>