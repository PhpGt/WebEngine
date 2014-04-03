<?php class SessionTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	require_once(GTROOT . "/Class/Session/Session.class.php");
	$_SESSION = array();
}

public function tearDown() {
	
}

// $this->assertContains(4, array(1, 2, 3));
// $this->assertEmpty(array('foo'));

public function testSessionExists() {
	$this->assertTrue(isset($_SESSION));
}

public function testSessionClassLoaded() {
	$this->assertTrue(is_callable(["Session", "set"]));
	$this->assertTrue(is_callable(["Session", "get"]));
}

public function testSessionSets() {
	Session::set("Test.Session.foo1", "bar1");
   Session::set("Test.Session.fooTrue", true);

   $value = Session::get("Test.Session.foo1");
	$this->assertEquals("bar1", $value);

   $value = Session::get("Test.Session.fooTrue");
   $this->assertTrue($value);
}

public function testSessionSetsArray() {
	Session::set("Test.Session", ["foo2" => "bar2"]);
	$value = Session::get("Test.Session.foo2");

	$this->assertEquals("bar2", $value);

	$array = Session::get("Test.Session");
	$this->assertEquals("bar2", $array["foo2"]);
}

public function testSessionSetsNamespaceArray() {
	Session::set(["Test", "Session", "DeepKey"], ["foo3" => "bar3"]);
	$array = Session::get("Test.Session.DeepKey");

	$this->assertContains("bar3", $array);
}

public function testSessionGetsNamespaceArray() {
	Session::set("Test.Session.DeepKey", ["foo4" => "bar4"]);
	$value = Session::get(["Test", "Session", "DeepKey", "foo4"]);

	$this->assertEquals("bar4", $value);
}

public function testSessionUnsets() {
	Session::set("Test.Session", ["foo8", "bar8"]);
	Session::set("Test.Session.Deep.Deeper.Namespace", ["foo9" => "bar9"]);

	Session::delete("Test.Session.Deep");

	$this->assertTrue(Session::exists("Test.Session"));
	$this->assertFalse(Session::exists("Test.Session.Deep.Deeper"));
}

public function testSessionPush() {
	Session::set("Test.pushable", ["Array with single element"]);
	Session::push("Test.pushable", "New element to push");

	$this->assertCount(2, Session::get("Test.pushable"));

	Session::push("Test.pushable", ["Sub-array"]);

	$this->assertCount(3, Session::get("Test.pushable"));

	$subArray = Session::pop("Test.pushable");
	$this->assertCount(2, Session::get("Test.pushable"));
	$this->assertCount(1, $subArray);

	Session::unshift("Test.pushable", "Element to test with");
	$this->assertCount(3, Session::get("Test.pushable"));
	$this->assertEquals(
		"Element to test with", Session::shift("Test.pushable"));
	$this->assertCount(2, Session::get("Test.pushable"));

	// Try to push on a namespace that isn't an array. Should become array.
	Session::set("Test.notArray", "This is not an array");
	Session::push("Test.notArray", "But now it should be");
	$this->assertCount(2, Session::get("Test.notArray"));

	Session::push("Test.newProperty", "First item");
	$this->assertCount(1, Session::get("Test.newProperty"));
}

}#