<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

class DispatcherFactory_Test extends \PHPUnit_Framework_TestCase {

public function setUp() {
	if(!defined("APP_NAMESPACE")) {
		define("APP_NAMESPACE", "TestApp");
	}
}

public function testDispatcherFactoryCreatesPageDispatcher() {
	$cfg = new \Gt\Core\ConfigObj();
	$request = $this->getMock("\Gt\Request\Request", ["getType"], [
		"/", $cfg,
	]);
	$request->expects($this->any())
		->method("getType")
		->will($this->returnValue(\Gt\Request\Request::TYPE_PAGE)
	);
	$response = $this->getMock("\Gt\Response\Response", null, [$cfg]);
	$api = $this->getMock("\Gt\Api\Api", null, [$cfg]);
	$session = $this->getMock("\Gt\Session\Session", null, [$cfg]);

	$dispatcher = DispatcherFactory::createDispatcher(
		"TestApp",
		$request,
		$response,
		$api,
		$session
	);

	$this->assertInstanceOf("\Gt\Dispatcher\PageDispatcher", $dispatcher);
}

public function testDispatcherFactoryCreatesApiDispatcher() {
	$cfg = new \Gt\Core\ConfigObj();
	$request = $this->getMock("\Gt\Request\Request", ["getType"], [
		"/", $cfg,
	]);
	$request->expects($this->any())
		->method("getType")
		->will($this->returnValue(\Gt\Request\Request::TYPE_API)
	);
	$response = $this->getMock("\Gt\Response\Response", null, [$cfg]);
	$api = $this->getMock("\Gt\Api\Api", null, [$cfg]);
	$session = $this->getMock("\Gt\Session\Session", null, [$cfg]);

	$dispatcher = DispatcherFactory::createDispatcher(
		"TestApp",
		$request,
		$response,
		$api,
		$session
	);

	$this->assertInstanceOf("\Gt\Dispatcher\ApiDispatcher", $dispatcher);
}

}#