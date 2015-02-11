<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class Obj_Test extends \PHPUnit_Framework_TestCase {

public function testObjPropertyDoesNotExist() {
	$obj = new Obj();
	$this->assertObjectNotHasAttribute("attributeName", $obj);
}

public function testObjPropertyIsCreated() {
	$obj = new Obj([], true);
	$obj->test = "testValue";

	$this->assertEquals("testValue", $obj->test);
}

public function testObjNestedPropertyIsCreated() {
	$obj = new Obj([], true);
	$obj->test->nested = "nestedValue";

	$this->assertObjectHasAttribute("test", $obj);
	$this->assertEquals("nestedValue", $obj->test->nested);
}

public function testObjConstructs() {
	$obj = new Obj([
		"testProperty" => "testValue",
	]);
	$this->assertEquals("testValue", $obj->testProperty);
}

public function testObjCallable() {
	$obj = new Obj([], false, true);
	$value = $obj->callMe();

	$this->assertInstanceOf("\Gt\Core\Obj", $value);
}

}#