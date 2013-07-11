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
 * All arrays that are passed into the autoRegister method will have their
 * key-value-pairs injected as variables into ALL SQL statements if their
 * placeholder exists.
 * This data is saved to the SESSION - to clear it, use clearRegister().
 * Note that auto registered parameters can be overridden by passing parameters 
 * to queries as usual.
 */
public function autoRegister($array /*, $array2, $arrayN*/ ) {
	$args = func_get_args();
	if(!isset($_SESSION["PhpGt_Dal_AutoRegister"])) {
		$_SESSION["PhpGt_Dal_AutoRegister"] = array();
	}
	foreach ($args as $a) {
		foreach ($a as $key => $value) {
			$_SESSION["PhpGt_Dal_AutoRegister"][$key] = $value;
		}
	}
}

/**
 * Removes any automatically registered values from the internal session.
 */
public function clearRegister() {
	if(isset($_SESSION["PhpGt_Dal_AutoRegister"])) {
		unset($_SESSION["PhpGt_Dal_AutoRegister"]);
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