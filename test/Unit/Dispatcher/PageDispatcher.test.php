<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

class PageDispatcher_Test extends \PHPUnit_Framework_TestCase {

public function testTest() {
	$request 	= $this->getMock("\Gt\Request\Request");
	$response	= $this->getMock("\Gt\Response\Reponse");
	$apiFactory	= $this->getMock("\Gt\Api\ApiFactory");
	$dbFactory	= $this->getMock("\Gt\Database\DatabaseFactory");

	$dispatcher = new PageDispatcher(
		$request, $response, $apiFactory, $dbFactory);

	$this->assertTrue(true);
}

}#