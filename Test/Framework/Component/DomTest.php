<?php 
namespace Framework\Component;
class DomTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", "/home/g105b/Webroot/PHP.Gt/");
	define("DS", DIRECTORY_SEPARATOR);
	require_once(GTROOT . DS . "Framework" . DS 
		. "Component" . DS . "Dom.php");
	require_once(GTROOT . DS . "Framework" . DS 
		. "Component" . DS . "DomEl.cmp.php");
	require_once(GTROOT . DS . "Framework" . DS 
		. "Component" . DS . "DomElCollection.cmp.php");
}

public function testDomIsDom() {
	$dom = new Dom();
	$dom2 = new Dom();
	$this->assertTrue(gettype($dom) === gettype($dom2));
}

}?>