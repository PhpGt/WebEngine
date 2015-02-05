<?php
/**
 * Represents a table-like structure and provides some basic interface methods
 * for typical data manipulation.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data;

abstract class Store {

private $idKey = "ID";

public function setIdKey($key) {
	$this->idKey = $key;
}

public function getById($value) {
	return $this->getBy($this->idKey, $value);
}

public function getBy($key, $value);

public function getAllBy($key, $value);

}#