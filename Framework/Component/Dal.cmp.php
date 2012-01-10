<?php
class Dal implements ArrayAccess {
	
	private $_dalElArray = array();

	public function __construct($config) {
		var_dump($config);die();
		try {
			$this->_dbh = new PDO(
				$config["ConnectionString"],
				$config["Username"],
				$config["Password"]
			);
		}
		catch (PDOException $e) {
			// TODO: Proper error handling.
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

		$this->_dalElArray[$offset] = new DalElement($this, $offset);
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
}
?>