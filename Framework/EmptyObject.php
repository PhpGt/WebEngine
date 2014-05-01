<?php final class EmptyObject implements ArrayAccess, Iterator {
/**
 * EmptyObject acts as a dummy object with infinite properties and keys for
 * special cases where the $dom, $template and $tool objects are not able to be
 * used, such as when there is a missing PageView file, but code is required to
 * be invoked before the 404 is emitted.
 */

public $length = 0;

public function offsetExists($offset) {
	return true;
}
public function offsetGet($offset) {
	return new EmptyObject();
}
public function offsetSet($offset, $value) {
	return true;
}
public function offsetUnset($offset) {
	return true;
}

public function next() {}
public function rewind() {}
public function valid() {
	return false;
}
public function current() {
	throw new \Exception("EmptyObject has no members");
}
public function key() {
	throw new \Exception("EmptyObject has no key");
}

public function __call($name, $args) {
	return new EmptyObject();
}
public function __get($offset) {
	return new EmptyObject();
}
public function __set($offset, $value) {
	return true;
}
}#