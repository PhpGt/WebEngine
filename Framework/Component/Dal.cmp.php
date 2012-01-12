<?php
/**
 * TODO: Docs.
 */
class Dal implements ArrayAccess {
	private $_dbh = null;
	private $_dalElArray = array();
	private $_paramChar = null;

	/**
	 * TODO: Docs.
	 */
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

	/**
	 * TODO: Docs.
	 */
	public function __destruct() {
		$this->_dbh = null;
	}

	/**
	 * TODO: Docs.
	 */
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
	
	/**
	 * TODO: Docs.
	 */
	public function offsetGet($offset) {
		$offset = ucfirst($offset);
		if(!$this->offsetExists($offset)) {
			// TODO: Proper error handling - DalObject doesn't exist.
			return null;
		}
		
		return $this->_dalElArray[$offset];
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetSet($offset, $value) {
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetUnset($offset) {
	}

	/**
	 * TODO: Docs.
	 */
	public function lastInsertId() {
		return $this->_dbh->lastInsertId();
	}

	/**
	 * TODO: Docs.
	 */
	public function prepare($sql) {
		return $this->_dbh->prepare($sql);
	}
}
?>