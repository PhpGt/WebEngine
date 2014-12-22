<?php
/**
 * CSV parsing and simple search/filter functions done out-of-memory.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Data;

class Csv implements Iterator {

private $path;

public function __construct($path) {
	if(!file_exists($path)) {
		$path = Path::get(Path::DATA) . "/$path";
	}

	if(!file_exists($path)) {
		throw new DataSourceNotFoundException($path);
	}

	$this->path = $path;
}

}#