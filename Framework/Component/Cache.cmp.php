<?php class Cache implements ArrayAccess {
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

public function __construct($type, $reference = null, $contextObj = null) {
	$this->_type = $type;
	$this->_reference = $reference;
	$this->_contextObj = $contextObj;
	$this->_cacheDir = APPROOT . DS . "Cache" . DS . $type;
}

public function __call($name) {
	// TODO: Wrap the context object's method, storing the output in cache.
}

/**
 * Checks the validity of the cache.
 */
public function offsetExists($offset) {
	switch($this->_type) {
	case "Database":
		return $this->checkValidDatabase($offset);
		break;
	default:
		die("Cache: Not yet implemented.");
		break;
	}
}

/**
 * Obtains data stored in the cache.
 */
public function offsetGet($offset) {
}

/**
 * Caches arbitrary data for use in recurring requests.
 */
public function offsetSet($offset, $value) {
}

/**
 * Used to invalidate the current cache.
 */
public function offsetUnset($offset) {
}

/**
 * Checks the validity of the cache for the supplied database name, if any.
 * Called externally by using isset($cacheObj["TableName"]).
 *
 * @return bool True if the cache is valid.
 */
private function checkValidDatabase($tableName) {
	$cacheDir = $this->_cacheDir . DS . $tableName;
	$touchFile = $cacheDir . ".dbtouch";
	$tableDir = $cacheDir . DS . $tableName;

	// If there is no tableDir, there definitely is no cache set.
	if(is_dir($tableDir)) {
		$touchT = file_exists($touchFile)
			? filemtime($touchFile)
			: 0;
		$tableT = filemtime($tableDir);
		if($tableT > $touchT) {
			return true;
		}
	}

	return false;
}

}?>