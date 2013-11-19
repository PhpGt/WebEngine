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

public function testSessionExtendsStorage() {
	Session::set("Test.Session", ["foo5" => "bar5"]);
	Session::set("Test.Session", ["foo6" => "bar6"]);
	Session::set("Test.Session.foo7", "bar7");

	$array = Session::get("Test.Session");

	$this->assertEquals("bar5", $array["foo5"]);
	$this->assertEquals("bar6", $array["foo6"]);
	$this->assertEquals("bar7", $array["foo7"]);
}

public function testSessionUnsets() {
	Session::set("Test.Session", ["foo8", "bar8"]);
	Session::set("Test.Session.Deep.Deeper.Namespace", ["foo9" => "bar9"]);

	Session::delete("Test.Session.Deep");

	$this->assertTrue(Session::exists("Test.Session"));
	$this->assertFalse(Session::exists("Test.Session.Deep.Deeper"));
}

}#