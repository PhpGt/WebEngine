<?php
final class ApiWrapper implements ArrayAccess {
	private $_dal = null;
	private $_apiElObjects = array();	// Cache of APIs used in this request.

	public function __construct($dal) {
		$this->_dal = $dal;
	}

	/**
	 * Attempts to locate and load the class file for the requested API module.
	 * Searches the application-specific directory before the shared directory.
	 * @param string $offset The name of the requested API module.
	 * @return bool|string Returns false on failure, or the name of the class
	 * on success.
	 */
	public function offsetExists($offset) {
		$offset = ucfirst($offset);
		return array_key_exists($offset, $this->_apiElObjects);
	}

	public function offsetGet($offset) {
		$offset = ucfirst($offset);

		if(!$this->offsetExists($offset)) {
			$this->_apiElObjects[$offset] = 
				new ApiElement($offset, $this->_dal);
		}

		return $this->_apiElObjects[$offset];
	}

	public function offsetSet($offset, $value) {
		// TODO: More appropriate error message and logging.
		die("What are you setting the API for???");
	}

	public function offsetUnset($offset) {
		// TODO: More appropriate error message and logging.
		die("What are you unsetting the API for???");
	}
}
?>