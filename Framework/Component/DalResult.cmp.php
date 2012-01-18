<?php
/**
 * TODO: Docs.
 */
final class DalResult implements Iterator, ArrayAccess {
	private $_stmt;
	private $_insertId;
	private $_originalSql;
	private $_tableName;
	private $_position;

	public $result;

	/**
	 * TODO: Docs.
	 */
	public function __construct($stmt, $insertId, $originalSql, $tableName) {
		$this->_stmt = $stmt;
		$this->_insertId = $insertId;
		$this->_originalSql = $originalSql;
		$this->_tableName = $tableName;
		$this->result = array();

		$this->_position = 0;

		$this->storeResult();
	}

	public function __get($key) {
		switch($key) {
		case "columnCount":
		case "columns":
		case "columnNum":
		case "numColumns":
		case "columnLength":
			return $this->_stmt->columnCount();
			break;
		case "rowCount":
		case "rows":
		case "rowNum":
		case "numRows":
		case "affectedRows":
			return $this->_stmt->rowCount();
			break;
		case "lastInsertId":
		case "insertId":
			return $this->_insertId;
			break;
		case "hasRows":
		case "hasResult":
		case "hasResults":
			return !empty($this->result);
			break;
		case "length":
			$this->storeResult(true);
			return count($this->result);
		default:
			// TODO: Throw proper error.
			die("Error: Invalid key requested from DalResult $tableName.");
			break;
		}
	}

	// Iterator ----------------------------------------------------------------
	public function current() {
		return $this->result[$this->_position];
	}

	public function key() {
		return $this->_position;
	}

	public function next() {
		++$this->_position;
		$this->storeResult();
	}

	public function rewind() {
		$this->_position = 0;
	}

	public function valid() {
		return isset($this->result[$this->_position]);
	}
	// End: Iterator -----------------------------------------------------------

	// ArrayAccess -------------------------------------------------------------
	public function offsetExists($offset) {
		$this->storeResult(true);

		if(!is_numeric($offset)) {
			// Looking for the first result's column.
			if(isset($this->result[0])) {
				return array_key_exists($offset, $this->result[0]);
			}
		}
				
		return array_key_exists($offset, $this->result);
	}

	public function offsetGet($offset) {
		$this->storeResult(true);
		
		if(!is_numeric($offset)) {
			if(isset($this->result[0])) {
				return $this->result[0][$offset];
			}
		}

		return $this->result[$offset];
	}

	public function offsetSet($offset, $value) {}
	public function offsetUnset($offset) {}
	// End: ArrayAccess --------------------------------------------------------

	private function storeResult($all = false) {
		if($all) {
			if(empty($this->result)) {
				if(false !== (
				$result = $this->_stmt->fetchAll(PDO::FETCH_ASSOC)) ) {
					$this->result = $result;
				}
			}
		}
		else {
			if(false !== ($result = $this->_stmt->fetch(PDO::FETCH_ASSOC))) {
				$this->result[] = $result;
			}
		}
	}
}
?>