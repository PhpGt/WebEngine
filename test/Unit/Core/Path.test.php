<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Path_Test extends \PHPUnit_Framework_TestCase {

public function setup() {
	$this->tmp = sys_get_temp_dir();
	$_SERVER["DOCUMENT_ROOT"] = $this->tmp;
}

public function testPathThrowsExceptionFromInvalidConstant() {
	$this->setExpectedException("\UnexpectedValueException");
	$invalidConstant = Path::get("spam");
}

public function testPathThrowsExceptionFromInvalidConstant() {
	$this->setExpectedException("\UnexpectedValueException");
	$invalidConstant = Path::get("spam");
}

public function testRootSet() {
	$this->assertEquals($this->tmp, Path::get(Path::ROOT));
}

public function testGetConfig() {
	
}

}#