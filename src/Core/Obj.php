<?php
/**
 * An Obj object represents an empty Object with no default properties.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

/**
 * @property mixed $anything The object can be given any read-write property.
 */
class Obj {

protected $autoNestProperties;
protected $autoCallMethods;

/**
 * @param array $params Associative array of parameters to add to this object
 * as properties
 * @param bool $autoNestProperties Whether to automatically instantiate new Obj
 * objects when non-existant properties are requested
 */
public function __construct($params = [],
$autoNestProperties = false, $autoCallMethods = false) {
	foreach ($params as $key => $value) {
		$this->$key = $value;
	}

	$this->autoNestProperties = $autoNestProperties;
	$this->autoCallMethods = $autoCallMethods;
}

/**
 * @param string $name Property name
 *
 * @return mixed Value of given property name
 */
public function __get($name) {
	if($this->autoNestProperties) {
		$this->$name = new Obj();
		return $this->$name;
	}
}

public function __call($name, $args) {
	if($this->autoCallMethods) {
		return new Obj([], $this->autoNestProperties, true);
	}
}

}#