<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Api;

use \Gt\Core\Path;

class ApiFactory {

private $config;
private $version;

private $componentArray = [];

public function __construct($config) {
	$this->config = $config;
	$this->version = $this->getVersionName($config->default_version);
}

public function __get($name) {
	$name = strtolower($name);

	if(array_key_exists($name, $this->componentArray)) {
		return $this->componentArray[$name];
	}

	$component = new Component($name, $this->version);
	$this->componentArray[$name] = $component;

	return $this->componentArray[$name];
}

/**
 * @param int $version The numerical version of the current API to use
 *
 * @return string The version name set according to provided version number
 */
public function setVersion($version) {
	if(!is_int($version)) {
		throw new \Gt\Core\InvalidArgumentException($version);
	}

	return $this->version = $this->getVersionName($version);
}

/**
 * @param int $version The numerical version of the current API
 *
 * @return string The version name according to the provided number
 */
private function getVersionName($version) {
	return $this->config->version_prefix
		. $version
		. $this->config->version_suffix;
}

private function getBaseDirectory() {
	$path = Path::get(Path::API) . "/" . $this->version;
	$path = Path::fixCase($path);
	return $path;
}

}#