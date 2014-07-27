<?php
/**
 * 
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

/**
 * @property mixed $anything The object can be given any read-write property.
 */
class Obj {

/**
 * @param array $params Associative array of parameters to add to this object
 * as properties.
 */
public function __construct($params = []) {
	foreach ($params as $key => $value) {
		$this->$key = $value;
	}
}

/**
 * Automatically creates properties that do not exist, then returns their value.
 * Only called when property does not exist.
 * @param string $name Name of property to return.
 */
public function __get($name) {
	$this->$name = new Obj();
	return $this->$name;
}

}#