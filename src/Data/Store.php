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

protected $connection;
protected $idKey = "ID";

public function __construct($connection) {
	$this->connection = $connection;
}

public function setIdKey($key) {
	$this->idKey = $key;
}

public function getById($value) {
	return $this->getBy($this->idKey, $value);
}

abstract public function getBy($key, $value);

abstract public function getAllBy($key, $value);

abstract public function getAll($fieldArray = []);

}#