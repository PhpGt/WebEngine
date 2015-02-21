<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data\Source;

use \Gt\Core\Path;
use \Gt\Data\Store\Csv as Store;

class Csv extends \Gt\Data\Source {

protected $sourcePath;
protected $storePool;

public function getStore($storeName) {
	$storePath = implode("/", [
		$this->sourcePath,
		"$storeName.csv",
	]);

	if(!isset($this->storePool[$storeName])) {
		$this->storePool[$storeName] = new Store($storePath);
	}

	return $this->storePool[$storeName];
}

}#