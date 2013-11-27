<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	createTestApp();
	require_once(GTROOT . "/Framework/FileOrganiser.php");
}

public function tearDown() {
	removeTestApp();
}

/**
 * There should only be one file within the www directory in a brand new app:
 * the Go.php, which is triggered by the webserver.
 */
public function testInitialWebrootIsEmpty() {
	$webroot = APPROOT . "/www";
	$diff = array_diff(["Go.php"], scandir($webroot));
	$this->assertEmpty($diff, "Unexpected www directory contents");
}


}#