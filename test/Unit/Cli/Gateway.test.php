<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class Gateway_Test extends \PHPUnit_Framework_TestCase {

public function setUp() {}

public function tearDown() {}

public function testStaticFileRequest() {
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/directory/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg?query=string"));
}

public function testDynamicRequest() {
	$this->assertFalse(Gateway::isStaticFileRequest("/"));
	$this->assertFalse(Gateway::isStaticFileRequest("/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/directory/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/?query=string"));
	$this->assertFalse(Gateway::isStaticFileRequest("/example?query=string"));
	$this->assertFalse(Gateway::isStaticFileRequest(
		"/example?query=string&file=picture.jpg"));
}

}#