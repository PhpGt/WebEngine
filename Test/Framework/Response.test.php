<?php class ResponseTest extends PHPUnit_Framework_TestCase {

private $_response;

public function setup() {
	removeTestApp();
	createTestApp();

	require_once GTROOT . "/Framework/Response.php";
	$this->_response = new Response(null);
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

	$pageViewFile = APPROOT . "/PageView/SubPath/Index.html";
	if(!is_dir(dirname($pageViewFile))) {
		mkdir(dirname($pageViewFile), 0775, true);
	}
	file_put_contents($pageViewFile, "This is 'Index', within 'SubPath'");

	$pageViewFile = APPROOT . "/PageView/SubPath/MyPage.html";
	if(!is_dir(dirname($pageViewFile))) {
		mkdir(dirname($pageViewFile), 0775, true);
	}
	file_put_contents($pageViewFile, "This is 'MyPage', within 'SubPath'");

	$originalPath = "/contact.html";
	$this->assertEquals("/Contact.html", 
		$this->_response->tryFixUrl($originalPath));

	$originalPath = "/contact";
	$this->assertEquals("/Contact.html", 
		$this->_response->tryFixUrl($originalPath));

	$originalPath = "/contact/";
	$this->assertEquals("/Contact.html", 
		$this->_response->tryFixUrl($originalPath));

	$originalPath = "/cOnTaCt";
	$this->assertEquals("/Contact.html", 
		$this->_response->tryFixUrl($originalPath));

	$originalPath = "/subpath";
	$this->assertEquals("/SubPath/Index.html", 
		$this->_response->tryFixUrl($originalPath));

	$originalPath = "/subpath/mypage";
	$this->assertEquals("/SubPath/MyPage.html", 
		$this->_response->tryFixUrl($originalPath));

	// Try something that can't be fixed.
	$originalPath = "/NonExistant";
	$this->assertFalse($this->_response->tryFixUrl($originalPath));
	$originalPath = "/contact/nothing.html";
	$this->assertFalse($this->_response->tryFixUrl($originalPath));
}

}#