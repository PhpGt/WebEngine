<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;
use \Gt\Core\Obj;

class Standardiser_Test extends \PHPUnit_Framework_TestCase {

public function setUp() {}

public function tearDown() {}

private $uriList = [
	"index",
	"about-me",
	"shop",
	"shop/pie/apple",
];

public function data_uriList() {
	$return = array(["/"]);

	foreach ($this->uriList as $uri) {
		$return []= ["/$uri"];
		$return []= ["/$uri/"];
		$return []= ["/$uri.html"];
		$return []= ["/$uri.html/"];
		$return []= ["/$uri.json"];
		$return []= ["/$uri.json/"];
		$return []= ["/$uri.jpg"];
		$return []= ["/$uri.jpg/"];
	}

	return $return;
}

/**
 * @dataProvider data_uriList
 */
public function testUriFixHtmlRemoved($uri) {
	$config = new Obj();
	$config->pageview_html_extension = false;
	$config->pageview_trailing_directory_slash = false;
	$standardiser = new Standardiser();

	$fixed = $standardiser->fixUri($uri, $config);
	$this->assertStringEndsNotWith(".html", $fixed);
}

/**
 * @dataProvider data_uriList
 */
public function testUriFixHtmlForced($uri) {
	$config = new Obj();
	$config->pageview_html_extension = true;
	$config->pageview_trailing_directory_slash = false;

	$standardiser = new Standardiser();

	$fixed = $standardiser->fixUri($uri, $config);

	$ext = pathinfo($uri, PATHINFO_EXTENSION);
	if(empty($ext) || $ext == "html") {
		$this->assertStringEndsWith(".html", $fixed);		
	}
}

/**
 * @dataProvider data_uriList
 */
public function testUriFixSlashForced($uri) {
	$ext = pathinfo($uri, PATHINFO_EXTENSION);
	$config = new Obj();
	$config->pageview_html_extension = false;
	$config->pageview_trailing_directory_slash = true;

	$standardiser = new Standardiser();

	$fixed = $standardiser->fixUri($uri, $config);

	if(empty($ext)) {
		$this->assertStringEndsWith("/", $fixed);
	}
	else {
		$this->assertStringEndsNotWith("/", $fixed);
	}
}

/**
 * @dataProvider data_uriList
 */
public function testUriIndexFilename($uri) {
	$file = pathinfo($uri, PATHINFO_FILENAME);
	$file = strtok($file, ".");
	$file = strtok($file, "?");
	$config = new Obj();
	$config->index_filename = "index";
	$config->index_force = false;

	$standardiser = new Standardiser();
	$fixed = $standardiser->fixUri($uri, $config);

	if($file === $config->index_filename) {
		$this->assertEquals(strtok($uri, "/index"), $fixed);
	}
	else {
		$this->assertEquals($uri, $fixed);
	}
}

/**
 * @dataProvider data_uriList
 */
public function testUriIndexForce($uri) {
	$config = new Obj();
	$config->index_force = true;
}

/**
 * @dataProvider data_uriList
 */
public function testUriIndexFilenameForce($uri) {
	$config = new Obj();
	$config->index_filename = "index";
	$config->index_force = true;


}

}#