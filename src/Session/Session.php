<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
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
	$namespace = $this->config->base_namespace;
	if(empty($namespace)) {
		$namespace = APP_NAMESPACE;
	}

	@session_start();

	if(!isset($_SESSION[$namespace])) {
		$_SESSION[$namespace] = new Store($this->config);
	}

	$this->store = $_SESSION[$namespace];
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

	$key = array_shift($nsArray);
	if(!isset($store[$key])) {
		if($direction === self::DATA_GET) {
			throw new SessionStoreNotFoundException($key);
		}
		else if($direction === self::DATA_SET) {

			if(empty($nsArray)) {
				//If setting an associative array, convert it to a Store.
				if($this->isAssociativeArray($value)) {

					$store[$key] = new Store($this->config);
					foreach ($value as $arrayKey => $arrayValue) {
						$arrayKey = $this->fixCase($arrayKey);
						$store[$key][$arrayKey] = $arrayValue;
					}
				}
				else {
					$store[$key] = $value;
				}
			}
			else {
				$store[$key] = new Store($this->config);
			}
		}
	}

	return $this->data(
		$direction,
		$store[$key],
		$nsArray,
		$value,
		$store[$key]
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
 * Detects an associative array using a simple decision of whether or not the
 * array has no integer keys. If there are not any integer keys, the array is
 * treated associative. If there are any integer keys, the array is treated as
 * not associative. If the array is empty, it is also treated as not
 * associative.
 *
 * @param array $array The array to test
 *
 * @return bool True if fully associative, false otherwise
 */
private function isAssociativeArray($array) {
	if(!is_array($array)) {
		return false;
	}

	if(empty($array)) {
		return false;
	}

	$integerKeys = array_filter(array_keys($array), "is_int");
	return (0 === count($integerKeys));
}

/**
 *
 */
private function getNamespaceArray($key) {
	$key = $this->fixCase($key);
	return explode($this->config->separator, $key);
}

}#