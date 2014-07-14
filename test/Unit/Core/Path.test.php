<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Path_Test extends \PHPUnit_Framework_TestCase {

private $root;

public function setUp() {
	$this->root = sys_get_temp_dir() . "/root";
	$_SERVER["DOCUMENT_ROOT"] = $this->root;
}

public function testPathRoot() {


	$root = Path::$root;
	var_dump($root);die();


	$this->assertEquals($this->root, $root);
}

}#