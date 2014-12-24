<?php
/**
 * CSV parsing and simple search/filter functions done out-of-memory.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Data;

use \Gt\Core\Path;
use \League\Csv\Reader;

class Csv { //implements \Iterator {

private $reader;
private $headers;

public function __construct($path) {
	if(!file_exists($path)) {
		$path = Path::get(Path::DATA) . "/$path";
	}

	if(!file_exists($path)) {
		throw new DataSourceNotFoundException($path);
	}

	$file = new \SplFileObject($path);
	$this->reader = Reader::createFromPath($file);
	$this->headers = $this->reader->fetchOne();
}

public function getAll() {
	$this->reader->addFilter(function($row, $index) {
		return $index > 0;
	});

	$result = $this->reader->fetchAll();

	// Replace indexes with header names.
	foreach ($result as $index => $resultItem) {
		foreach ($result[$index] as $key => $value) {
			$result[$index][$this->headers[$key]] = $value;
			unset($result[$index][$key]);
		}
	}

	return $result;
}

/**
 * Return a dataset where the matching column key is the provided value.
 *
 * @param string $key Column name to search on
 * @param string $value Value to filter by
 * @param bool $strict Use strict type checking
 *
 * @return ???
 */
public function findBy($key, $value, $strict = false) {
	$rowIndex = array_search($key, $this->headers);
	$columnCount = count($this->headers);

	if($rowIndex === false) {
		throw new InvalidFilterKeyException($key);
	}

	$this->reader->addFilter(function($row, $index) {
		// Ignore header row.
		return $index > 0;
	});

	$this->reader->addFilter(function($row, $index) use($columnCount) {
		// Only return rows with valid columns.
		for($i = 0; $i < $columnCount; $i++) {
			if(!isset($row[$i])) {
				return false;
			}
		}

		return true;
	});

	$this->reader->addFilter(function($row, $index)
	use($rowIndex, $value, $strict) {
		if($strict) {
			return ($row[$rowIndex] === $value);
		}
		else {
			return ($row[$rowIndex] == $value);
		}
	});

	$result = $this->reader->fetchAll();

	// Replace indexes with header names.
	foreach ($result as $index => $resultItem) {
		foreach ($result[$index] as $key => $value) {
			$result[$index][$this->headers[$key]] = $value;
			unset($result[$index][$key]);
		}
	}

	return $result;
}

}#