<?php class DalElement {
/**
 * TODO: Docs.
 */
private $_dal = null;
private $_tableName = null;
private $_paramChar = null;

public function __construct($dal, $tableName, $paramChar) {
	$this->_dal = $dal;
	$this->_tableName = $tableName;
	$this->_paramChar = $paramChar;
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
		APPROOT . DS . "Database" . DS . $this->_tableName . DS,
		GTROOT  . DS . "Database" . DS . $this->_tableName . DS
	);
	$fileName = ucfirst($name) . ".sql";

	$sql = null;
	foreach($pathArray as $path) {
		if(file_exists($path . $fileName)) {
			return $this->query($path . $fileName, $args);
			break;
		}
	}

	// TODO: Throw proper error.
	die("Error: No SQL found for $this->_tableName called $name.");
	return false;
}

private function query($sqlFile, $paramArray = array()) {		
	if(!is_array($paramArray)) {
		// TODO: Throw proper error.
		die("Error: Type of query params is not an array");
	}
	$sql = file_get_contents($sqlFile);

	foreach ($paramArray as $key => $value) {
		unset($paramArray[$key]);
		$key = $this->_paramChar . $key;
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
	// :Limit and :Offset (upper-case first letters).
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

	try {
		$result = $stmt->execute();
		// Find out the number of affected rows (SQL for portability).
		$rowCountStmt = $this->_dal->prepare("select row_count() as RowCount");
		$rowCountStmt->execute();
		$rowCountResult = $rowCountStmt->fetch();
		if($rowCountResult[0] > 0) {
			$this->touchCache();
		}
		return new DalResult(
			$stmt,
			$this->_dal->lastInsertId(), 
			$sql, 
			$this->_tableName);
	}
	catch(PDOException $e) {
		$this->_dal->fixError($e);
		return false;
	}
}

/**
 * Every time a table changes in the database, a file is touched in the Cache
 * directory. The file has the name of the changed table. This is used by the 
 * caching system to defer connecting to the database if nothing has changed.
 */
private function touchCache() {
	$cacheDir = APPROOT . DS . "Cache" . DS . "Database";
	$cacheFile = $this->_tableName . ".dbtouch";

	if(!is_dir($cacheDir)) {
		mkdir($cacheDir, 0777, true);
	}
	touch($cacheDir . DS . $cacheFile);
}

}?>