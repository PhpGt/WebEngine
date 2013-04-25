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
 * @param mixed $context Optional. Any object that you wish to use as the cached
 * resource. Most commonly will be an ApiEl, but could be any custom object too.
 */
public function __construct($context = null) {
	if(!is_null($context)) {
		$this->_contextObj = $context;
		if($context instanceof PageCode) {
			$this->_type = "Page";
			$this->setPageCache();
		}
		else if($context instanceof ApiEl) {
			$this->_type = "Database";
			$this->_reference = $context->getName();
		}
		// TODO: More type checks.
		else {
			$this->_type = "Custom";
		}
	}

	$this->_cacheDir = APPROOT . "/Cache/$this->_type";
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
	$cacheFile = $files["TableDir"] . "/$name.dbobj";

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
		case "Page":
			return $this->checkValidPage();
			break;
		case "Database":
			return $this->checkValidDatabase();
			break;
		default:
			die("Cache: Not yet implemented.");
			break;
		}

		break;
	case "timestamp":
	case "timestamps":

		switch($this->_type) {
		case "Database":
			return $this->getTimeStampDatabase();
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
 * Attempts to render the current requested page from cache. This will terminate
 * all scripts if successful, else it will quietly fail.
 *
 * @param array $dependencies Optional. Pass either an array of Cache objects,
 * or a list of Cache objects as multiple parameters to add to the dependency
 * checks. Useful for when a page is dependent on multiple database tables.
 */
public function tryRender($dependencies = array()) {
	if(!$this->valid) {
		return;
	}
	// Fail rendering cache if any of the dependencies are invalid.
	if(!empty($dependencies)) {
		foreach ($dependencies as $dependency) {
			if(!$dependency->valid) {
				return;
			}
		}
	}
	$file = $this->getPageFile();
	if(!file_exists($file)) {
		return;
	}
	$filemtime = filemtime($file);
	$html = file_get_contents($file);
	echo "<!-- PHP.Gt cache modified: " 
		. date("d/m/y h:i:s", $filemtime) 
		. " ($filemtime) -->";
	echo $html;
	exit;
}

/**
 * Deletes the current cached data, if any.
 */
public function invalidate() {
	switch($this->_type) {
	case "Page":
		$file = $this->getPageFile();
		unlink($file);
		$_SESSION["PhpGt_Cache"]["Page"] = false;
		break;
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
		"TouchFile" => $this->_cacheDir . "/$this->_reference.dbtouch",
		"TableDir"	=> $this->_cacheDir . "/$this->_reference/.",
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
		$timeStamps = $this->getTimestampDatabase();
		
		if($timeStamps["TableDir"] > $timeStamps["TouchFile"]) {
			return true;
		}
		if(isset($_SESSION["PhpGt_Cache"]["Database"][$this->_reference])) {
			$sessionT = $_SESSION["PhpGt_Cache"]["Database"][$this->_reference];
			if($sessionT <= $timeStamps["TableDir"]) {
				return true;
			}
		}
	}

	return false;
}

private function setPageCache() {
	if(empty($_SESSION["PhpGt_Cache"])) {
		$_SESSION["PhpGt_Cache"] = array();
	}
	if(empty($_SESSION["PhpGt_Cache"]["Page"])) {
		$_SESSION["PhpGt_Cache"]["Page"] = true;
	}
}

private function getPageFile() {
	return $this->_cacheDir . "/" . DIR . "/" . FILE . "." . EXT;
}

private function checkValidPage() {
	$file = $this->getPageFile();
	if(file_exists($file)) {
		if(!empty($_SESSION["PhpGt_Cache"]["PageView_mtime"])) {
			$filemtime = filemtime($file);
			$_SESSION["PhpGt_Cache"]["PageView_mtime"];
			return $filemtime > $_SESSION["PhpGt_Cache"]["PageView_mtime"];
		}
	}
	return false;
}

/**
 * @return int The unix timestamp of the current cached database table.
 */
private function getTimeStampDatabase() {
	$timeStamps = array(
		"TouchFile" => 0,	// Last Db Modification
		"TableDir" => 0		// Last Cache
	);

	$files = $this->getDatabaseFiles();
	$timeStamps["TouchFile"] = file_exists($files["TouchFile"])
		? filemtime($files["TouchFile"])
		: 0;

	$tableDirMax = 0;
	if(is_dir($files["TableDir"])) {
		$dirIt = new DirectoryIterator($files["TableDir"]);
		foreach ($dirIt as $fileInfo) {
			if(!$fileInfo->isFile()) {
				continue;
			}
			$filemtime = $fileInfo->getMTime();
			if($filemtime > $tableDirMax) {
				$tableDirMax = $filemtime;
			}
		}
	}
	$timeStamps["TableDir"] = $tableDirMax;

	return $timeStamps;
}

}?>