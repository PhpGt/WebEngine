<?php class ApiWrapper implements ArrayAccess {
/**
 * This object is passed into the go() methods as the $api parameter. It acts
 * as an associative array to the ApiEl objects.
 */

private $_apiElObjects = array();	// Cache of APIs used in this request.

public $dal = null;

public function __construct($dalOrApiWrapper) {
	if($dalOrApiWrapper instanceof Dal) {
		$this->dal = $dalOrApiWrapper;
	}
	else {
		$this->dal = $dalOrApiWrapper->dal;
	}
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
	if($offset instanceof PageTool) {
		$offset = get_class($offset);
	}
	else {
		$offset = ucfirst($offset);
	}

	if(!$this->offsetExists($offset)) {
		$this->_apiElObjects[$offset] = 
			new ApiEl($offset, $this->dal);
	}

	return $this->_apiElObjects[$offset];
}

// Can't set/unset the API values.
public function offsetSet($offset, $value) {}
public function offsetUnset($offset) {}

}#