<?php
/**
 * Represents the HTTP request header fields by normalising the $_SERVER array's
 * keys that start with "HTTP_". Notice: These headers are sent by the requester
 * and should not be trusted!
 * 
 * Can be accessed as an associative array to reference headers in their 
 * original format (i.e. $headers["accept-language"]) or as object properties
 * with underscores (i.e. $headers->accept_language).
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;
use \Gt\Core\Obj;

class HeaderFields implements \ArrayAccess {

private $headerArray = [];

public function __construct($serverArray) {
	foreach ($serverArray as $key => $value) {
		if(strpos($key, "HTTP_") !== 0) {
			continue;
		}

		$headerName = str_replace("_", "-", substr($key, 5));
		$headerName = strtolower($headerName);
		$this->headerArray[$headerName] = $value;
	}
}

public function offsetExists($offset) {
	$offset = strtolower($offset);
	return isset($this->headerArray[$offset]);
}

public function offsetGet($offset) {
	$offset = strtolower($offset);
	return $this->headerArray[$offset];
}

public function offsetSet($offset, $value) {
	throw new \Gt\Core\Exception\InvalidAccessException();
}

public function offsetUnset($offset) {
	throw new \Gt\Core\Exception\InvalidAccessException();
}

public function __get($name) {
	$name = strtolower($name);
	
	$name = str_replace("_", "-", $name);
	return $this->headerArray[$name];
}

}#