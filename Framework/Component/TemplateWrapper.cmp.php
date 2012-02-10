<?php
class TemplateWrapper implements ArrayAccess {
	private $_templateArray = null;

	public function __construct($templateArray) {
		$this->_templateArray = $templateArray;
	}
	
	public function offsetExists($offset) {
		return isset($_this->_templateArray[$offset]);
	}

	/**
	 * Returns a cloned DomEl of the requested template.
	 */
	public function offsetGet($offset) {
		if($this->offsetExists($offset)) {
			$domEl = $this->_templateArray[$offset];
			return $domEl->cloneNode(true);
		}

		// TODO: Throw error here.
		return null;
	}

	public function offsetSet($offset, $value) {
		// TODO: More appropriate error message and logging.
		die("What are you setting the Template for???");
	}

	public function offsetUnset($offset) {
		// TODO: More appropriate error message and logging.
		die("What are you unsetting the Template for???");
	}
}
?>