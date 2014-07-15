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
class Config {

public function __construct() {
	$configPath = Path::get(Path::ROOT) . "/config.ini";
	$configArray = parse_ini_file($configPath, false);

	foreach ($configArray as $key => $value) {
		$this->$key = $value;
	}
}

}#