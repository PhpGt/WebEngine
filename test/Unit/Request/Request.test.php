<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

use \Gt\Core\ConfigObj;

class Request_Test extends \PHPUnit_Framework_TestCase {

private $uriTypeArray = [
	"/",
	"/index",
	"/index/",
	"/index.html",
	"/index.html/",
	"page",
	"page/",
	"page.html",
	"page.html/",
	"/dir/nested",
	"/dir/nested/",
	"/dir/nested.html",
	"/dir/nested.html/",
];

public function data_uriType() {
	$return = [];

	foreach ($this->uriTypeArray as $uriType) {
		$return [] = [$uriType];
		$return [] = ["/api"		. $uriType];
		$return [] = ["/myapi"		. $uriType];
		$return [] = ["/service"	. $uriType];
	}

	return $return;
}

/**
 * @dataProvider data_uriType
 */
public function testGetType($uri) {
	$objArray = [
		new ConfigObj(["api_directory" => "api"], true),
		new ConfigObj(["api_directory" => "myapi"], true),
		new ConfigObj(["api_directory" => "service"], true),
	];

	foreach ($objArray as $obj) {
		$obj->setName("api");
		\Gt\Core\Path::setConfig($obj);

		$request = new Request($uri, new ConfigObj());
		$type = $request->getType();

		if(strpos($uri, "/" . $obj->api_directory) === 0) {
			$this->assertEquals(Request::TYPE_API, $type);
		}
		else {
			$this->assertEquals(Request::TYPE_PAGE, $type);
		}
	}
}

}#