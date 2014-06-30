<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Test;
class GtServer_Test extends \PHPUnit_Framework_TestCase {

public function setUp() {
	require GTROOT . "/bin/gtserver.php";
}
// public function tearDown() {}

public function testTrue() {
	$this->assertTrue(true);
}

public function testTrueAgain() {
	$this->assertTrue(true);
}

}#