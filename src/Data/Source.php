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

use \Gt\Core\Path;

abstract class Source {

protected $sourcePath;
protected $storePool;

public function __construct($sourcePath) {
	$absoluteSourcePath = $sourcePath;
	if(!is_dir($absoluteSourcePath)) {
		$absoluteSourcePath = implode("/", [
			Path::get(Path::DATA),
			$absoluteSourcePath,
		]);
	}
	if(!is_dir($absoluteSourcePath)) {
		mkdir($absoluteSourcePath, 0775, true);
	}
	$this->sourcePath = $absoluteSourcePath;
}

/**
 *
 */
public abstract function getStore($storeName);

/**
 *
 */
public function getTable($tableName) {
	return $this->getStore($tableName);
}

}#