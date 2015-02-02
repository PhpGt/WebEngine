<?php
/**
 * Loads the config.ini file from the application root and parses it into this
 * object's properties, for use within Gt and the application.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class Config implements \ArrayAccess {

const DEFAULT_CONFIG_FILE = "default.ini";
private $configArray = [];

public function __construct($defaultConfigArray = null) {
	$configPath = Path::get(Path::ROOT) . "/config.ini";
	if(is_null($defaultConfigArray)) {
		$defaultConfigPath =
			Path::get(Path::GTROOT)
			. "/"
			. self::DEFAULT_CONFIG_FILE;
		$defaultConfigArray = parse_ini_file($defaultConfigPath, true);
	}

	$config = [];
	if(file_exists($configPath)) {
		$config = parse_ini_file($configPath, true);
	}
	$this->configArray = array_replace_recursive(
		$defaultConfigArray,
		$config
	);

	// Ensure lowercase configuration keys:
	foreach ($this->configArray as $key => $config) {
		$lcKey = strtolower($key);
		$this->configArray[$lcKey] = array_merge(
			$config, $this->configArray[$lcKey]);
		if($lcKey !== $key) {
			unset($this->configArray[$key]);
		}
	}
}

public function offsetExists($offset) {
	return isset($this->configArray[$offset]);
}

public function offsetGet($offset) {
	$obj = new ConfigObj();
	$obj->setName($offset);

	if(isset($this->configArray[$offset])) {
		foreach ($this->configArray[$offset] as $key => $value) {
			$obj->$key = $value;
		}
	}

	return $obj;
}

public function offsetSet($offset, $value) {}
public function offsetUnset($offset) {}

}#