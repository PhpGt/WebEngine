<?php class DalEl {
/**
 * This object represents a Table Collection, on which it can execute queries.
 * Any method can be called, which will be wrapped directly to the corresponding
 * SQL file or stored procedure.
 *
 * At this point in the code, the optional API class has done whatever it needs
 * to, and we can safely assume that there should be an SQL file representing
 * the called methods.
 */
private $_dal		= null;
private $_tableName	= null;
private $_isTool	= false;

public function __construct($dal, $tableName, $isTool = false) {
	$this->_dal = $dal;
	$this->_tableName = $tableName;
	$this->_isTool = $isTool;
}

public function __call($name, $args) {
	while(isset($args[0])) {
		if(!is_array($args[0])) {
			break;
		}
		$args = $args[0];
	}

	// Find the appropriate SQL file, perform SQL using $this->_dal;
	$pathArray = array(
		APPROOT . "/Database/{$this->_tableName}/",
		GTROOT  . "/Database/{$this->_tableName}/"
	);
	$fileName = ucfirst($name) . ".sql";

	if($this->_isTool) {
		$toolPathArray = array(
			APPROOT . "/PageTool/{$this->_tableName}/Database/",
			GTROOT  . "/PageTool/{$this->_tableName}/Database/",
		);
		$pathArray = array_merge($toolPathArray, $pathArray);
	}

	foreach($pathArray as $path) {
		if(file_exists($path . $fileName)) {
			return $this->query($path . $fileName, $args);
			break;
		}
	}

	throw new HttpError(500, 
		"No SQL found for $this->_tableName called $name.");
	return false;
}

public function setTool() {
	$this->_isTool = true;
}

private function query($sqlFile, $paramArray = array()) {		
	if(!is_array($paramArray)) {
		throw new HttpError(500, 
			"Trying to query $this->_tableName with incorrect parameters.");
	}
	$sql = file_get_contents($sqlFile);

	// There may be some parameters set to auto-register. Note that auto
	// registered parameters can be overridden by passing parameters to queries
	// as usual.
	$autoRegister = isset($_SESSION["PhpGt_Dal_AutoRegister"])
		? $_SESSION["PhpGt_Dal_AutoRegister"]
		: array();
	$paramArray = array_merge($autoRegister, $paramArray);

	foreach ($paramArray as $key => $value) {
		if($value === "null") {
			$value = null;
		}
		unset($paramArray[$key]);
		$key = ":" . $key;
		$paramArray[$key] = $value;

		// Remove any params that don't actually occur in the SQL...
		// Find occurences with trailing commas or spaces (to avoid matching
		// "Id" with "IdAS400" for example).
		$match = preg_quote($key, "/");
		if(preg_match("/{$match}\b/i", $sql) === 0) {
			unset($paramArray[$key]);
		}
	}

	// Up until now, there may be no connection present. The connect method
	// ensures there is a connection, but doesn't create a new one each time.
	$this->_dal->connect();
	$stmt = $this->_dal->prepare($sql);

	// Limit and offset params must be treated differently. This means that
	// a limitation in the SQL is that the parameters must be called
	// :Limit and :offset (upper-case first letters).
	if(array_key_exists(":Limit", $paramArray)) {
		$stmt->bindValue(":Limit", $paramArray[":Limit"], PDO::PARAM_INT);
		unset($paramArray[":Limit"]);
	}
	if(array_key_exists(":Offset", $paramArray)) {
		$stmt->bindValue(":Offset", $paramArray[":Offset"], PDO::PARAM_INT);
		unset($paramArray[":Offset"]);
	}

	foreach ($paramArray as $key => &$value) {
		if($value instanceof DateTime) {
			$value = $value->format("Y-m-d H:i:s");
		}
		$stmt->bindParam($key, $value);
	}

	// The database may not be deployed yet. It will automatically deploy, but
	// will need to re-execute the statement once deployed.
	$tries = 0;
	while($tries <= 10) {
		try {
			$stmt->closeCursor();
			$result = $stmt->execute();

			// Find out the number of affected rows.
			$rowCount = $stmt->rowCount();
			if($rowCount > 0) {
				$this->touchCache();
			}
			return new DalResult(
				$stmt,
				$this->_dal->lastInsertID(), 
				$sql, 
				$this->_tableName);
		}
		catch(PDOException $e) {
			$this->_dal->autoDeploy($e);
		}
		$tries ++;
	}
	// Database can't be deployed.
	return false;
}

/**
 * Every time a table changes in the database, a file is touched in the Cache
 * directory. The file has the name of the changed table. This is used by the 
 * caching system to defer connecting to the database if nothing has changed.
 */
private function touchCache() {
	$cacheDir = APPROOT . "/Cache/Database";
	$cacheFile = $this->_tableName . ".dbtouch";

	if(!is_dir($cacheDir)) {
		mkdir($cacheDir, 0777, true);
	}
	
	touch("$cacheDir/$cacheFile");

	// In some situations i.e. unit testing, a cache miss is directly followed
	// by a cache hit, but the filemtime set by touching the file is only
	// measured in seconds... Setting a session variable allows more exact 
	// cache measurement.
	if(empty($_SESSION["PhpGt_Cache"])) {
		$_SESSION["PhpGt_Cache"] = array();
	}
	if(empty($_SESSION["PhpGt_Cache"]["Database"])) {
		$_SESSION["PhpGt_Cache"]["Database"] = array();
	}
	$_SESSION["PhpGt_Cache"]["Database"][$this->_tableName] = microtime(true);
}

}#