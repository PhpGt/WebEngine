<?php
/**
 * Represents a CSV file, treating it with table-like structure.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data\Source\Csv;

class CsvStore extends \Gt\Data\Store {

private $csv;

public function __construct($connection) {
	parent::__construct($connection);

	$this->csv = new \Gt\ThirdParty\Csv($this->connection);
}

public function getBy($key, $value) {
}

public function getAllBy($key, $value) {
}

public function getAll($fieldArray = []) {
}

}#