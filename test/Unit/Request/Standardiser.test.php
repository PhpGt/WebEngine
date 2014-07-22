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
public function testUrlFixHtmlRemoved($uri) {
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
public function testUrlFixHtmlForced($uri) {
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
public function testUrlFixSlashForced($uri) {
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

}#