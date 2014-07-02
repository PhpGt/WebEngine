<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Path_Test extends \PHPUnit_Framework_TestCase {

public function testPathThrowsExceptionFromInvalidConstant() {
	$this->setExpectedException("\UnexpectedValueException");

	$invalidConstant = Path::INVALID_CONSTANT;
}

}#