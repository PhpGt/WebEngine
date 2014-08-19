<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
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
		new ConfigObj(["api_prefix" => "api"], true),
		new ConfigObj(["api_prefix" => "myapi"], true),
		new ConfigObj(["api_prefix" => "service"], true),
	];

	foreach ($objArray as $obj) {
		$request = new Request($uri, $obj);
		$type = $request->getType();

		if(strpos($uri, "/" . $obj->api_prefix) === 0) {
			$this->assertEquals(Request::TYPE_API, $type);
		}
		else {
			$this->assertEquals(Request::TYPE_PAGE, $type);
		}
	}
}

}#