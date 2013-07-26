<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", getcwd() . "/../");
	require_once(GTROOT . "Framework/FileOrganiser.php");
}

public function testInitialWebrootIsEmpty() {
}

}#