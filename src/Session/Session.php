<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Session;

class Session {

private $config;
private $store;

const DATA_GET = "data_get";
const DATA_SET = "data_set";

const STATUS_ACTIVE = PHP_SESSION_ACTIVE;
const STATUS_INACTIVE = PHP_SESSION_DISABLED;

public function __construct($config) {
	$this->config = $config;

	@session_start();

	if(!isset($_SESSION[$this->config->base_namespace])) {
		$_SESSION[$this->config->base_namespace] = new Store($this->config);
	}

	$this->store = $_SESSION[$this->config->base_namespace];
}

public function getStatus() {
	return session_status();
}

public function setConfig($config) {
	$this->config = $config;
}

/**
 *
 */
public function get($key) {
	$nsArray = $this->getNamespaceArray($key);
	$data = $this->data(self::DATA_GET, $this->store, $nsArray);

	return $data;
}

/**
 *
 */
public function set($key, $value) {
	$nsArray = $this->getNamespaceArray($key);
	return $this->data(self::DATA_SET, $this->store, $nsArray, $value);
}

/**
 * Recursive function to iterate through nested Store objects, returning the
 * most nested (leaf) value. Set $value to SOMETHING to retrieve the value in
 * the nested Store rather than setting it.
 *
 * @param Store $store The root Store to iterate upon
 * @param array $nsArray Array of namespace key names
 * @param mixed $value The value to set the leaf Store to
 * @param mixed $return The value of the current iteration, used to exit from
 * the recursive iteration
 *
 * @return mixed The value contained by the leaf Store
 */
private function data($direction, $store, $nsArray,
$value = null, $return = null) {
	if(empty($nsArray)) {
		return $return;
	}

	$getKey = array_shift($nsArray);
	if(!isset($store[$getKey])) {
		if($direction === self::DATA_GET) {
			throw new SessionStoreNotFoundException($getKey);
		}
		else if($direction === self::DATA_SET) {


			if(empty($nsArray)) {
				$store[$getKey] = $value;
			}
			else {
				$store[$getKey] = new Store($this->config);
			}
		}
	}

	return $this->data(
		$direction,
		$store[$getKey],
		$nsArray,
		$value,
		$store[$getKey]
	);
}

/**
 *
 */
public function exists($key) {
	$key = $this->fixCase($key);
	return isset($this->store[$key]);
}

/**
 *
 */
public function delete($key) {
	$key = $this->fixCase($key);
	unset($this->store[$key]);
}

/**
 *
 */
private function fixCase($key) {
	if(!$this->config->case_sensitive) {
		return strtoupper($key);
	}

	return $key;
}

/**
 *
 */
private function getNamespaceArray($key) {
	$key = $this->fixCase($key);
	return explode($this->config->separator, $key);
}

}#