<?php
/**
 * Defines a set of basic data fetching methods found across all types of
 * Data Store.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data;

abstract class Fetcher {

private $idKey = "ID";

public function setIdKey($key) {
	$this->idKey = $key;
}

public function getById($value) {
	return $this->getBy($this->idKey, $value);
}

public function getBy($key, $value) {

}

public function getAllBy($key, $value) {

}



}#