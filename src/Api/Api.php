<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Api;

use \Gt\Core\Path;

class Api {

private $config;
private $version;
private $responseContent;
private $componentArray = [];

public $session;

/**
 *
 */
public function __construct($config, $responseContent = null, $session = null) {
	$this->config = $config;
	$this->responseContent = $responseContent;
	$this->session = $session;
	$this->version = $this->getVersionName($config->default_version);
}

/**
 *
 */
public function __get($name) {
	$name = strtolower($name);

	if(array_key_exists($name, $this->componentArray)) {
		return $this->componentArray[$name];
	}

	$component = new Component($name, $this);
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
 *
 */
public function getVersion() {
	return $this->version;
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