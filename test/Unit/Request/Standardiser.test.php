<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

use \Gt\Core\ConfigObj;

class Standardiser_Test extends \PHPUnit_Framework_TestCase {

public function setUp() {}

public function tearDown() {}

private $uriList = [
	"index",
	"about-me",
	"shop",
	"shop/pie/apple",
];

private $queryStringList = [
	"",
	"?sort=date",
	"?first=Rasmus&last=Lerdorf"
];

private $indexNameList = [
	"index",
	"start",
	"home",
];

public function data_uriList() {
	$return = array(["/"]);

	foreach ($this->uriList as $uri) {
		foreach ($this->queryStringList as $qs) {
			$return []= ["/$uri"		. $qs];
			$return []= ["/$uri/"		. $qs];
			$return []= ["/$uri.html"	. $qs];
			$return []= ["/$uri.html/"	. $qs];
			$return []= ["/$uri.json"	. $qs];
			$return []= ["/$uri.json/"	. $qs];
			$return []= ["/$uri.jpg"	. $qs];
			$return []= ["/$uri.jpg/"	. $qs];
		}
	}

	return $return;
}

public function data_uriList_withIndexName() {
	$return = $this->data_uriList();

	foreach ($return as $i => $param) {
		foreach($this->indexNameList as $indexName) {
			$return[$i] []= $indexName;
		}
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
		$uri, $file, $ext, new ConfigObj()) );

	$config = new ConfigObj();
	$config->force_extension = false;

	$fixed = $standardiser->fixHtmlExtension($uri, $file, $ext, $config);
	$this->assertNotRegexp("/\.html.?$/", $fixed);

	$config = new ConfigObj();
	$config->force_extension = true;

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
			$this->assertRegexp("/\.html.?(\?.+)?$/", $fixed);
		}
		else {
			$this->assertNotRegexp("/\.html.?(\?.+)?$/", $fixed);
		}
	}

	$queryString = parse_url($uri, PHP_URL_QUERY);
	if(!empty($queryString)) {
		$this->assertContains("?" . $queryString, $fixed);
	}
}

/**
 * @dataProvider data_uriList_withIndexName
 */
public function testFixIndexFilenameForce($uri, $index) {
	$this->pathinfo($uri, $file, $ext);

	$config = new ConfigObj();
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
 * @dataProvider data_uriList_withIndexName
 */
public function testFixIndexFilenameNoForce($uri, $index) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();
	$this->assertEquals($uri,
		$standardiser->fixIndexFilename($uri, $file, $ext, new ConfigObj()) );

	$config = new ConfigObj();
	$config->index_force = false;
	$config->index_filename = $index;

	$fixed = $standardiser->fixIndexFilename($uri, $file, $ext, $config);

	if($file === $index
	&&(empty($ext) || $ext === "html") ) {
		$expected = substr($uri, 0, strrpos($uri, $index));
		$this->assertEquals($expected, $fixed, "The ext is $ext");
	}
	else {
		$this->assertEquals($uri, $fixed);
	}
}

/**
 * @dataProvider data_uriList
 */
public function testFixTrailingSlash($uri) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();
	$this->assertEquals($uri,
		$standardiser->fixTrailingSlash($uri, $ext, new ConfigObj()));

	$config = new ConfigObj();
	$config->pageview_trailing_directory_slash = true;

	$fixed = $standardiser->fixTrailingSlash($uri, $ext, $config);

	$lastChar = substr($uri, -1);
	if(empty($ext)) {
		if($lastChar === "/") {
			$this->assertEquals($uri, $fixed);
		}
		else {
			$this->assertEquals($uri . "/", $fixed);
		}
	}
}

/**
 * @dataProvider data_uriList
 */
public function testFixNoTrailingSlash($uri) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();
	$this->assertEquals($uri,
		$standardiser->fixTrailingSlash($uri, $ext, new ConfigObj()));

	$config = new ConfigObj();
	$config->pageview_trailing_directory_slash = false;

	$fixed = $standardiser->fixTrailingSlash($uri, $ext, $config);

	$lastChar = substr($uri, -1);
	if(empty($ext)) {
		if($lastChar === "/") {
			// Make sure URIs always start with a slash.
			if($uri === "/") {
				$this->assertEquals($uri, $fixed);
			}
			else {
				$this->assertEquals(substr($uri, 0, -1), $fixed);
			}
		}
		else {
			$this->assertEquals($uri, $fixed);
		}
	}
}

/**
 * @dataProvider data_uriList
 */
public function testFixTrailingExtSlash($uri) {
	$this->pathinfo($uri, $file, $ext);
	$standardiser = new Standardiser();
	$fixed = $standardiser->fixTrailingExtSlash(
		$uri, $ext);

	if(!empty($ext)) {
		$this->assertStringEndsNotWith("/", $fixed);
	}
}

/**
 * @dataProvider data_uriList
 */
public function testQueryStringPreserved($uri) {
	$standardiser = new Standardiser();
	$fixed = $standardiser->fixUri($uri, new ConfigObj());

	$queryString = parse_url($uri, PHP_URL_QUERY);
	if(!empty($queryString)) {
		$this->assertContains("?" . $queryString, $fixed);
	}
}

/**
 * The above tests ensure that the functionality of fixUri is as expected.
 * This test simply asserts that there are not exceptions thrown, and the fixUri
 * method does in fact return a string.
 *
 * @dataProvider data_uriList
 */
public function testFixUri($uri) {
	$standardiser = new Standardiser();

	$fixed = $standardiser->fixUri("$uri", new ConfigObj() );
	$this->assertInternalType("string", $fixed);
}

}#