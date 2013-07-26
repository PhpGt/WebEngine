<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	define("APPROOT", getcwd() . "/TestApp");
	createTestApp();
	require_once(GTROOT . "Framework/FileOrganiser.php");
}

public function tearDown() {
	removeTestApp();
}

public function testInitialWebrootIsEmpty() {
	$webroot = APPROOT . "/www";
	$diff = array_diff(["Go.php"], scandir($webroot));
	$this->assertEmpty($diff, "Unexpected www directory contents");
}

}#