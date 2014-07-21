<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;
use \Gt\Core\Obj;

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
	}

	return $return;
}

/**
 * @dataProvider data_uriType
 */
public function testGetType($uri) {
	$ext = pathinfo($uri, PATHINFO_EXTENSION);

	$objArray = [
		new Obj(),
		new Obj(),
	];

	$objArray[1]->pageview_html_extension = true;

	foreach ($objArray as $obj) {
		$request = new Request($uri, $obj);
		$type = $request->getType();
		
		if(empty($ext)) {
			$this->assertEquals(Request::TYPE_PAGE, $type);
		}
		else if($obj->pageview_html_extension) {
			if($ext === "html") {
				$this->assertEquals(Request::TYPE_PAGE, $type);
			}
			else {
				$this->assertEquals(Request::TYPE_SERVICE, $type);
			}
		}
		else {
			$this->assertEquals(Request::TYPE_SERVICE, $type);	
		}
	}
}

}#