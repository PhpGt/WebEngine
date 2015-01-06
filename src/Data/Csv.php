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
use \League\Csv\Writer;

class Csv {

private $reader;
private $file;
private $headers;

public function __construct($path) {
	if(!file_exists($path)) {
		$path = Path::get(Path::DATA) . "/$path";
	}

	if(!file_exists($path)) {
		throw new DataSourceNotFoundException($path);
	}

	$this->file = new \SplFileObject($path, "r+");
	$this->reader = Reader::createFromPath($this->file);
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
 *
 */
public function getLast() {
	$all = $this->reader->fetchAll();
	$last = [];
	$i = count($all) - 1;

	while(empty($last) || empty($last[0]) ){
		$last = $all[$i];
		$i--;
	}

	foreach ($last as $key => $value) {
		$last[$this->headers[$key]] = $value;
		unset($last[$key]);
	}

	return $last;
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
		// Only return rows with at least the forename column.
		$forenameIndex = array_search("Forename", $this->headers);
		if(!isset($row[$forenameIndex])) {
			return false;
		}
		// // Only return rows with valid columns.
		// for($i = 0; $i < $columnCount; $i++) {
		// 	if(!isset($row[$i])) {
		// 		return false;
		// 	}
		// }

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

/**
 *
 */
public function add($data) {
	// $writer = $this->reader->newWriter();
	$this->file->fseek(0, SEEK_END);

	// while($this->file->fread(1) !== "\n") {
	// 	$this->file->fseek(-1);
	// }

	// $this->file->fwrite("\n");
	$this->file->fputcsv($data);
	// var_dump($this->file, $this->reader);die();
	// $writer->insertOne($data);
	// var_dump($writer, $data);die("writer");
}

}#