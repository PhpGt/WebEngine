<?php class ApiWrapper implements ArrayAccess {
/**
 * TODO: Docs.
 */

private $_dal = null;
private $_apiElObjects = array();	// Cache of APIs used in this request.
private $_tool = false;

public function __construct($dal, $tool = false) {
	$this->_dal = $dal;
	$this->_tool = $tool;
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
			new ApiEl($offset, $this->_dal, $this->_tool);
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

}?>