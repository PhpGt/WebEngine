<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

class PageDispatcher_Test extends \PHPUnit_Framework_TestCase {

private $dispatcher;

public function setUp() {
	$cfg = new \Gt\Core\ConfigObj();

	$request 	= $this->getMock("\Gt\Request\Request", null, [
		"/", $cfg,
	]);
	$response	= $this->getMock("\Gt\Response\Reponse", null);/*, [
		$cfg
	]);*/
	$apiFactory	= $this->getMock("\Gt\Api\ApiFactory", null, [
		$cfg
	]);
	$dbFactory	= $this->getMock("\Gt\Database\DatabaseFactory", null, [
		$cfg
	]);

	$this->dispatcher = new PageDispatcher(
		$request, $response, $apiFactory, $dbFactory);
}

public function testDispatcherCreated() {
	$this->assertInstanceOf("\Gt\Dispatcher\PageDispatcher",
		$this->dispatcher);
}

}#