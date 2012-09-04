<?php class Cache {
/**
 * The PHP.Gt Cache Handler allows bi-directional access to cached resources
 * from anywhere in the code. Within PageCode or PageTool, cached resources
 * can be created first by instantiating the cache object and using the object
 * as an array to get and set cache data.
 */

private $_type = null;
private $_reference = null;
private $_contextObj = null;
private $_cacheDir;

/**
 * @param string $type What type of cache to use. Not only does this indicate
 * the name of the subdirectory of where the cache is stored, but has different
 * effects for some types, such as "Database" checks the .dbtouch files.
 * @param string $reference An optional reference to an area of the current
 * type's cache. For Database type, the reference is the cached TableCollection.
 * @param mixel $contextObj To provide a short-hand automatic cached object,
 * methods can be called on this object which will return cached data or
 * alternatively return the output of the method on the context object. 
 */
public function __construct($type, $reference = null, $contextObj = null) {
	$this->_type = $type;
	$this->_reference = $reference;
	$this->_contextObj = $contextObj;
	$this->_cacheDir = APPROOT . DS . "Cache" . DS . $type;
	if(!is_dir($this->_cacheDir)) {
		mkdir($this->_cacheDir, 0777, true);
	}
}

/**
 * Wraps the context object's method, storing the output in cache.
 */
public function __call($name, $args) {
	if(is_null($this->_contextObj)) {
		// TODO: Throw proper error.
		die("ERROR: Cache context not set.");
	}
	$files = $this->getDatabaseFiles();
	$cacheFile = $files["TableDir"] . DS . $name;

	if($this->valid) {
		if(file_exists($cacheFile)) {
			$fileContents = file_get_contents($cacheFile);
			if($fileContents !== false) {
				$object = unserialize($fileContents);
				if($object !== false) {
					return $object;
				}
			}
		}
	}
	// Either cache is invalid, or cache object hasn't been made yet - call
	// non-cached data source.
	$result = call_user_func_array([$this->_contextObj, $name], $args);
	// Store the cached object.
	$serialized = serialize($result);
	if(!is_dir($files["TableDir"])) {
		mkdir($files["TableDir"], 0777, true);
	}
	file_put_contents($cacheFile, $serialized);
	return $result;
}

public function __get($name) {
	switch($name) {
	case "valid":

		switch($this->_type) {
		case "Database":
			return $this->checkValidDatabase();
			break;
		default:
			die("Cache: Not yet implemented.");
			break;
		}

		break;
	case "timestamp":

		switch($this->_type) {
		case "Database":
			return $this->getTimestampDatabase();
			break;
		default:
			die("Cache: Not yet implemented.");
			break;
		}

		break;
	default:
		break;
	}
}

/**
 * Deletes the current cached data, if any.
 */
public function invalidate() {
	switch($this->_type) {
	case "Database":
		$files = $this->getDatabaseFiles();
		touch($files["TouchFile"]);
		break;
	default:
		break;
	}
}

private function getDatabaseFiles() {
	return array(
		"TouchFile" => $this->_cacheDir . DS . $this->_reference . ".dbtouch",
		"TableDir"	=> $this->_cacheDir . DS . $this->_reference
	);
}
/**
 * Checks the validity of the cache for the supplied database name, if any.
 * Called externally by using isset($cacheObj["TableName"]).
 *
 * @return bool True if the cache is valid.
 */
private function checkValidDatabase() {
	$files = $this->getDatabaseFiles();
	// If there is no tableDir, there definitely is no cache set.
	if(is_dir($files["TableDir"])) {
		$touchT = file_exists($files["TouchFile"])
			? filemtime($files["TouchFile"])
			: 0;
		$tableT = filemtime($files["TableDir"]);
		if($tableT > $touchT) {
			return true;
		}
	}

	return false;
}

/**
 * @return int The unix timestamp of the current cached database table.
 */
private function getTimestampDatabase() {
	$files = $this->getDatabaseFiles();
	// If there is no tableDir, there definitely is no cache set.
	if(is_dir($files["TableDir"])) {
		return filemtime($files["TouchFile"]);
	}

	return 0;
}

}?>