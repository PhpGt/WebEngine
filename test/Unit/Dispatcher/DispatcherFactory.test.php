<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

class DispatcherFactory_Test extends \PHPUnit_Framework_TestCase {

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
	$apiFactory = $this->getMock("\Gt\Api\ApiFactory", null, [$cfg]);
	$dbFactory = $this->getMock("\Gt\Database\DatabaseFactory", null, [$cfg]);

	$dispatcher = DispatcherFactory::createDispatcher(
		"TestApp",
		$request,
		$response,
		$apiFactory,
		$dbFactory
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
	$apiFactory = $this->getMock("\Gt\Api\ApiFactory", null, [$cfg]);
	$dbFactory = $this->getMock("\Gt\Database\DatabaseFactory", null, [$cfg]);

	$dispatcher = DispatcherFactory::createDispatcher(
		"TestApp",
		$request,
		$response,
		$apiFactory,
		$dbFactory
	);

	$this->assertInstanceOf("\Gt\Dispatcher\ApiDispatcher", $dispatcher);
}

}#