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

private function pathinfo($uri, &$file, &$ext) {
	$pathinfo = pathinfo($uri);
	$file = strtok($pathinfo["filename"], "?");
	$ext  = empty($pathinfo["extension"])
		? null
		: strtok($pathinfo["extension"], "?");
}

/**
 * @dataProvider data_uriList
 */
public function testFixHtmlExtension($uri) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();

	$this->assertEquals($uri, $standardiser->fixHtmlExtension(
		$uri, $file, $ext, new Obj()) );

	$config = new Obj();
	$config->pageview_html_extension = false;

	$fixed = $standardiser->fixHtmlExtension($uri, $file, $ext, $config);
	$this->assertNotRegexp("/\.html.?$/", $fixed);

	$config = new Obj();
	$config->pageview_html_extension = true;

	$fixed = $standardiser->fixHtmlExtension($uri, $file, $ext, $config);
	if(empty($ext)) {
		if($uri === "/") {
			$this->assertEquals($fixed, $uri);
		}
		else {
			$this->assertRegexp("/\.html.?$/", $fixed);			
		}
	}
	else {
		if($ext === "html") {
			$this->assertRegexp("/\.html.?$/", $fixed);			
		}
		else {
			$this->assertNotRegexp("/\.html.?$/", $fixed);			
		}
	}
}

/**
 * @dataProvider data_uriList
 */
public function testFixIndexFilenameForce($uri) {
	$this->pathinfo($uri, $file, $ext);

	$index = "index";
	$config = new Obj();
	$config->index_force = true;
	$config->index_filename = $index;

	$standardiser = new Standardiser();
	$fixed = $standardiser->fixIndexFilename($uri, $file, $ext, $config);

	if(empty($file)) {
		$expected = "$uri$index";
		$this->assertEquals($expected, $fixed);
	}
}

/**
 * @dataProvider data_uriList
 */
public function testFixIndexFilenameNoForce($uri) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();
	$this->assertEquals($uri, 
		$standardiser->fixIndexFilename($uri, $file, $ext, new Obj()) );

	$index = "index";
	$config = new Obj();
	$config->index_force = false;
	$config->index_filename = $index;

	$fixed = $standardiser->fixIndexFilename($uri, $file, $ext, $config);

	if($file === $index 
	&&(empty($ext) || $ext === "html") ) {
		$expected = substr($uri, 0, strrpos($uri, $index));
		$this->assertEquals($expected, $fixed, "The ext is $ext");
	}
}

}#