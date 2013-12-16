<?php class ResponseTest extends PHPUnit_Framework_TestCase {

public function setup() {
	removeTestApp();
	createTestApp();
}

public function tearDown() {
	removeTestApp();
}

/**
 * When a requested page is not found, try fixing the URL by case/directory.
 * This allows case-insensitive URLs and missing .html extensions.
 */
public function testUrlFixed() {
	// Create the Contact.html PageView
	$pageViewFile = APPROOT . "/PageView/Contact.html";
	if(!is_dir(dirname($pageViewFile))) {
		mkdir(dirname($pageViewFile), 0775, true);
	}
	file_put_contents($pageViewFile, "Contact page");

	$originalPath = "/contact";
	header("Location: Test");
}

}#