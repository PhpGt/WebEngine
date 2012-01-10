<?php
class DalElement {
	private $_dal;
	private $_tableName;

	public function __construct($dal, $tableName) {
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

	private function query($sqlFile, $paramArray) {
		$sql = file_get_contents($sqlFile);
		
	}
}
?>