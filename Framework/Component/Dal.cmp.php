<?php
class Dal implements ArrayAccess {
	private $_dbh = null;
	private $_dalElArray = array();
	private $_paramChar = null;

	public function __construct($config) {
		try {
			$this->_dbh = new PDO(
				$config["ConnectionString"],
				$config["Username"],
				$config["Password"]
			);

			$this->_paramChar = $config["ParamChar"];
		}
		catch (PDOException $e) {
			// TODO: Proper error handling.
			// In development mode, show help message to how to create database.
			// Output SQL to create database and all users.
			die("ERROR: Connection failed. " . $e->getMessage());
		}
	}

	public function __destruct() {
		$this->_dbh = null;
	}

	public function offsetExists($offset) {
		// First, check cache to see if DalObject already exists.
		if(array_key_exists($offset, $this->_dalElArray)) {
			return true;
		}

		$this->_dalElArray[$offset] = new DalElement(
			$this,
			$offset,
			$this->_paramChar
		);

		return true;
	}
	
	public function offsetGet($offset) {
		$offset = ucfirst($offset);
		if(!$this->offsetExists($offset)) {
			// TODO: Proper error handling - DalObject doesn't exist.
			return null;
		}
		
		return $this->_dalElArray[$offset];
	}

	public function offsetSet($offset, $value) {
	}

	public function offsetUnset($offset) {
	}

	public function lastInsertId() {
		return $this->_dbh->lastInsertId();
	}

	public function prepare($sql) {
		return $this->_dbh->prepare($sql);
	}
}
?>