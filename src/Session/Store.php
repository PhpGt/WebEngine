<?php
/**
 * Represents a branch of a session namespace.
 * Session storage Shop.Basket.Intl.value has three stores (Shop that contains
 * Basket, that in turn contains Intl, that in turn contains the key "value").
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Session;

class Store implements \ArrayAccess {

private $storage = [];

public function __construct($config) {
	$this->caseSensitive = $config->case_sensitive;
}

public function offsetExists($key) {
	return array_key_exists($key, $this->storage);
}
public function offsetGet($key) {
	return $this->storage[$key];
}
public function offsetSet($key, $value) {
	$this->storage[$key] = $value;
}
public function offsetUnset($key) {
	unset($this->storage[$key]);
}

}#