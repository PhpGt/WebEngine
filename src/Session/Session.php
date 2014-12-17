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

public function __construct($config) {
	$this->config = $config;
	session_start();
	if(!isset($_SESSION[$this->config->base_namespace])) {
		$_SESSION[$this->config->base_namespace] = new Store($this->config);
	}

	$this->store = $_SESSION[$this->config->base_namespace];
}


/**
 *
 */
public function get($key) {
	return $this->store[$key];
}

/**
 *
 */
public function exists($key) {
	return isset($this->store[$key]);
}

/**
 *
 */
public function set($key, $value) {
	$this->store[$key] = $value;
}

/**
 *
 */
public function delete($key) {
	unset($this->store[$key]);
}

/**
 *
 */
private function fixCase($key) {
	if(!$config->case_sensitive) {
		return strtoupper($key);
	}

	return $key;
}

/**
 *
 */
private function getNamespaceArray($string) {
	$nsArray = array();

	if(is_string($ns)) {
		$nsArray = explode(".", $ns);
	}
	else if(is_array($ns)) {
		$nsArray = $ns;
	}
	else {
		// TODO: throw exception
		die("getNsArray error!!!");
	}

	return $nsArray;
}

/**
 * Initialises a nested array and returns reference to the deepest (the leaf).
 *
 * @param array $arrayContainer description
 */
private function initLeaf(&$arrayContainer, $nsToInit, &$leaf = null) {
	if(empty($nsToInit)) {
		return $leaf;
	}

	$initKey = array_shift($nsToInit);

	if(!isset($arrayContainer[$initKey])) {
		$arrayContainer[$initKey] = array();
	}

	return self::initLeaf(
		$arrayContainer[$initKey],
		$nsToInit,
		$arrayContainer[$initKey]
	);
}

}#