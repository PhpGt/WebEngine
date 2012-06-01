<?php
class TemplateWrapper implements ArrayAccess {
	private $_templateArray = array();

	public function __construct($templateArray) {
		$this->_templateArray = $templateArray->getElArray();
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
			return $domEl->cloneNode(true);
		}

		// TODO: Throw error here.
		return null;
	}

	public function offsetSet($offset, $value) {
		if(!$this->offsetExists($offset)) {
			// TODO: Throw proper error.
			die("ERROR: Trying to set a non-existant template element.");
		}
		$this->_templateArray[$offset] = $value;

		// TODO: Update JavaScript templates.
	}

	public function offsetUnset($offset) {
		// TODO: More appropriate error message and logging.
		die("What are you unsetting the Template for???");
	}
}
?>