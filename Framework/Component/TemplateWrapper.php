<?php class TemplateWrapper implements ArrayAccess, Iterator {
private $_templateArray = array();
private $_index = 0;

public function __construct($templateArray) {
	$this->_templateArray = $templateArray->getElArray();
}

public function getArray() {
	return $this->_templateArray;
}

public function offsetExists($offset) {
	return array_key_exists($offset, $this->_templateArray);
}

/**
 * Returns a cloned DomEl of the requested template.
 */
public function offsetGet($offset) {
	if($this->offsetExists($offset)) {
		$domEl = $this->_templateArray[$offset];
		if($domEl->tagName == "template") {
			$domEl = $domEl->firstChild;
		}
		return $domEl->cloneNode(true);
	}

	throw new Exception("Template $offset does not exist.");
}

public function offsetSet($offset, $value) {
	if(!$this->offsetExists($offset)) {
		// TODO: Throw proper error.
		die("ERROR: Trying to set a non-existent template element.");
	}
	$this->_templateArray[$offset] = $value;

	// ALPHATODO: This may already work, needs testing.
	// TODO: Update JavaScript templates.
}

public function offsetUnset($offset) {
	// TODO: More appropriate error message and logging.
	die("What are you unsetting the Template for???");
}

public function current() {
	return $this->_templateArray[$this->_index];
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
	return isset($this->_templateArray[$this->_index]);
}

}#