<?php
class DalElement {
	private $_dal = null;
	private $_tableName = null;
	private $_paramChar = null;

	public function __construct($dal, $tableName, $paramChar) {
		$this->_dal = $dal;
		$this->_tableName = $tableName;
	}

	public function __call($name, $args) {
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
		return false;
	}

	private function query($sqlFile, $paramArray = array()) {
		if(!is_array($paramArray)) {
			// TODO: Throw proper error.
			die("Error: Type of query params is not an array");
		}
		$sql = file_get_contents($sqlFile);

		var_dump($paramArray);

		$stmt = $this->_dal->prepare($sql, $paramArray);
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

		var_dump($paramArray);

		die("Got here");

		$this->_dal->execute($paramArray);
		/** 
		MEGATODO: Execute the prepared statement, do something with the result!
		Are we going to use PDO objects? I hope so!

		I'm seeing an unintended error: In the Todo part of TestApp, exactly as
		it stands now, all paramArray keys are being removed. Is there something
		wrong with the regular expression?
		**/
	}
}
?>