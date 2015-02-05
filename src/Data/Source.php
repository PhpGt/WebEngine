<?php
/**
 * Represents a data source for persistent storage in an app using simple CSV,
 * SQL, or a remote source such as Google Sheets.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data;

abstract class Source {

protected $params;

public function __construct($params) {
	$this->params = $params;
}

/**
 * Gets a Data Store for the given named table-like object.
 *
 * @param string $name Name of Data Store within current source
 *
 * @return \Gt\Data\Store
 */
abstract public function getStore($name);

/*
 * Synonym for getStore
 */
public function getTable($name) {
	return $this->getStore($name);
}

}#