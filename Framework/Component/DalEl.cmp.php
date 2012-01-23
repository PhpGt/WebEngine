<?php
class DalElement {
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

		$stmt = $this->_dal->prepare($sql);
		if($stmt->execute($paramArray)) {
			return new DalResult(
				$stmt,
				$this->_dal->lastInsertId(), 
				$sql, 
				$this->_tableName);
		}
		else {
			// Grab the statment's error message.
			$errorInfo = $stmt->errorInfo();
			$error = $errorInfo[2];
			$patternArray = array(
				"NOTABLE" => "/^Table '(.*)' doesn't exist/"
			);

			// Find known error messages.
			foreach ($patternArray as $patternName => $pattern) {
				$matchArray = array();
				if(preg_match($pattern, $error, &$matchArray) > 0) {
					// Dispatch known error to DAL to attempt fix.
					$this->_dal->fixError($patternName, $matchArray);
				}
			}

			// FixError should replace Location header on success.
			// At this point, there is no fix, an SQL error is output.
			// TODO: Proper error handling.
			var_dump($errorInfo);
			die("Error executing SQL!");
		}
	}
}
?>