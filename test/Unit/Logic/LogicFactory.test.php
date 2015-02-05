<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Logic;

use \Gt\Core\Path;
use \Gt\Request\Request;

class LogicFactory_Test extends \PHPUnit_Framework_TestCase {

private $uriList = [
	"/index",
	"/test-page",
	"/directory/inner-page",
	"/directory/subdirectory/nested-page",
];
private $api;

public function setUp() {
	$_SERVER["DOCUMENT_ROOT"] = sys_get_temp_dir() . "/www";

	$cfg = $this->getMock("\Gt\Core\ConfigObj");
	$this->api = $this->getMock("\Gt\Api\Api", null, [$cfg]);
}

public function tearDown() {

}

public function data_uri() {
	$return = [];

	foreach ($this->uriList as $uri) {
		$return []= [$uri];
	}

	return $return;
}

/**
 * @dataProvider data_uri
 */
public function testGetLogicFileArrayGivesCommons($uri) {
	$topPath = Path::get(Path::PAGE);
	$filename = basename($uri);
	$path = pathinfo($topPath . $uri, PATHINFO_DIRNAME);

	$logicFileArray = LogicFactory::getLogicFileArray(
		$filename,
		$path,
		$topPath
	);

	// Check each directory in the tree.
	$directory = $topPath . $uri;
	do {
		$directory = dirname($directory);
		$commonPath = $directory . "/_common.php";
		$this->assertContains($commonPath, $logicFileArray,
			"Message, and TODO: output actual array. ($commonPath)"
			. print_r($logicFileArray, true));
	} while(strstr($directory, $path . "/"));
}

/**
 * @dataProvider data_uri
 */
public function testGetLogicFileArray($uri) {
	$topPath = Path::get(Path::PAGE);
	$filename = basename($uri);
	$path = pathinfo($topPath . $uri, PATHINFO_DIRNAME);

	$logicFileArray = LogicFactory::getLogicFileArray(
		$filename,
		$path,
		$topPath
	);

	// Assert that there is a _Common Page Logic class filename for each
	// directory, plus the name of the requested file.
	$directoryCount = substr_count($uri, "/");
	$this->assertCount($directoryCount + 1, $logicFileArray);
}

/**
 * @dataProvider data_uri
 */
public function testGetLogicClassNameArray($uri) {
	$root = Path::get(Path::ROOT);
	$topPath = Path::get(Path::PAGE);
	$logicSubPathArray = [
		"/Index.php",
		"/_Common.php",
		"/Directory/Index.php",
		"/Directory/SubPage.php",
		"/Directory/InnerDirectory/AnotherPage.php",
	];
	$logicPathArray = [];
	foreach ($logicSubPathArray as $subPath) {
		$logicPathArray []= $topPath . $subPath;
	}

	$logicClassNameArray = LogicFactory::getLogicClassNameArray(
		"TestApp",
		$logicPathArray,
		$topPath
	);
	$logicClassNameArray = array_map("strtolower", $logicClassNameArray);

	foreach ($logicSubPathArray as $i => $subPath) {
		// Create a FQ class name that should exist in the logicClassNameArray.
		$expectedFQClassNamePrefix = "TestApp\\Page";
		$expectedFQClassName =
			$expectedFQClassNamePrefix
			. str_replace("/", "\\", $subPath);
		$expectedFQClassName = strtok($expectedFQClassName, ".");

		$this->assertContains(
			strtolower($expectedFQClassName),
			$logicClassNameArray
		);

		$expectedFQCommonClassName = $expectedFQClassName;

		while(substr_count(
			$expectedFQClassName, $expectedFQClassNamePrefix) > 1) {
			$expectedFQCommonClassName = substr(
				$expectedFQCommonClassName,
				0,
				strrpos($expectedFQCommonClassName, "\\")
			);
			$this->assertContains(
				strtolower($expectedFQCommonClassName . "\\_common"),
				$logicClassNameArray
			);
		}
	}
}

}#