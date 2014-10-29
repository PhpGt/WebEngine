<?php
/**
 * Client side compilation is handled by third party libraries that must have
 * full test-suites associated. This test case is intended to test how PHP.Gt
 * interfaces with these libraries, rather than the functionality of the
 * libraries themselves.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \scssc as ScssParser;

class Compiler_Test extends \PHPUnit_Framework_TestCase {

private $tmp;

public function setUp() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

public function testCompilesScss() {
	$filePath = $this->tmp . "/file.scss";
	$source = '$red: rgb(225, 16, 32); a { color: $red; }';
	file_put_contents($filePath, $source);
	// Regular expressions used to ignore white space.
	$output = preg_replace("/\s/", "", Compiler::parse($filePath));
	$expected = preg_replace("/\s/", "", "a { color: #e11020;}");

	$this->assertEquals($expected, $output);
}

}#