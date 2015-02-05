<?php
/**
 * Represents a directory on disk with database-like behaviour, providing an
 * interface for accessing Data Stores for each CSV file within the directory.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data\Source\Csv;

use \Gt\Core\Path;

class CsvSource {

private $path;

public function __construct($params = null) {
	$path = Path::get(Path::DATA);

	if(!is_null($params)) {
		$path = $params["path"];
	}

	$this->path = $path;
}

/**
 * Gets a Data Store for a CSV file within the currently represented directory.
 *
 * @param string $name Name of Data Store within current source
 *
 * @return \Gt\Data\Store
 */
public function getStore($name) {
	$storePath = Path::fixCase(implode("/", [
		$this->path,
		$name,
	]));

	if(!is_dir($storePath)) {
		mkdir($storePath, 0775, true);
	}

	return new CsvStore($storePath);
}

}#