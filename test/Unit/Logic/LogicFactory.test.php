<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
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
private $apiFactory;
private $dbFactory;

public function setUp() {
	$_SERVER["DOCUMENT_ROOT"] = sys_get_temp_dir() . "/www";

	$cfg = $this->getMock("\Gt\Core\ConfigObj");
	$this->apiFactory = $this->getMock("\Gt\Api\ApiFactory", null, [$cfg]);
	$this->dbFactory = $this->getMock("\Gt\Database\DatabaseFactory", null, [
		$cfg
	]);
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
	$topPath = Path::get(Path::PAGELOGIC);
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
		$this->assertContains($commonPath, $logicFileArray);
	} while(strstr($directory, $path));
}

/**
 * @dataProvider data_uri
 */
public function testGetLogicFileArray($uri) {
	$topPath = Path::get(Path::PAGELOGIC);
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

// public function testCreatesPageLogicForTestPage($uri) {
// 	$logicList = LogicFactory::create(
// 		"TestApp",
// 		$uri,
// 		Request::TYPE_PAGE,
// 		$this->apiFactory,
// 		$this->dbFactory,
// 		$this->content
// 	);
// }

}#