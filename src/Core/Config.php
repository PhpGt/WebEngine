<?php
/**
 * Loads the config.ini file from the application root and parses it into this
 * object's properties, for use within Gt and the application.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;
class Config implements \ArrayAccess {

private $configArray = [];

public function __construct() {
	$configPath = Path::get(Path::ROOT) . "/config.ini";
	$this->configArray = parse_ini_file($configPath, true);
}

public function offsetExists($offset) {
	return isset($this->configArray[$offset]);
}

public function offsetGet($offset) {
	$obj = new \StdClass();

	foreach ($this->configArray[$offset] as $key => $value) {
		$obj->$key = $value;
	}

	return $obj;
}

public function offsetSet($offset, $value) {}
public function offsetUnset($offset) {}

}#