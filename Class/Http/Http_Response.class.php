<?php class Http_Response implements ArrayAccess, Iterator {
private $_responseArray = array();
private $_index = 0;

public function add($data) {
	$this->_responseArray[] = $data;
}

public function offsetExists($offset) {
	if(is_string($offset)
	&& array_key_exists($offset, $this->_responseArray[0])) {
		return true;
	}

	return isset($this->_responseArray[$offset]);
}

public function offsetGet($offset) {
	if(is_string($offset)
	&& array_key_exists($offset, $this->_responseArray[0])) {
		return $this->_responseArray[0][$offset];
	}
	
	return $this->_responseArray[$offset];
}

public function offsetSet($offset, $value) {
	$this->_responseArray[$offset] = $value;
}

public function offsetUnset($offset) {
	unset($this->_responseArray[$offset]);
}

public function current() {
	return $this->responseArray[$this->_index];
}

public function key() {
	return $this->_index;
}

public function next() {
	++$this->_index;
}

public function rewind() {
	$this->_index = 0;
}

public function valid() {
	return $this->offsetExists($this->_index);
}

}#