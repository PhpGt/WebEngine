<?php
/**
 * Represents a branch of a session namespace.
 * Session storage Shop.Basket.Intl.value has three stores (Shop that contains
 * Basket, that in turn contains Intl, that in turn contains the StoreValue
 * represented by value).
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Session;

class Store implements \ArrayAccess {

private $tempStorage = [];

public function __construct($config) {
	$this->caseSensitive = $config->case_sensitive;
}

public function offsetExists($key) {
	return isset($this->tempStorage[$key]);
}
public function offsetGet($key) {
	return $this->tempStorage[$key];
}
public function offsetSet($key, $value) {
	$this->tempStorage[$key] = $value;
}
public function offsetUnset($key) {
	unset($this->tempStorage[$key]);
}

}#