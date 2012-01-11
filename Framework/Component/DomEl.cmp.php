<?php
class DomEl implements ArrayAccess {
	public $node;
	private $_dom;

	/**
	* A wrapper to PHP's native DOMElement, adding more object oriented
	* features to be more like JavaScript's implementation.
	*/
	public function __construct(
	$dom,
	$element,
	$attrArray  = null,
	$value      = null) {

		$this->_dom = $dom;

		if($element instanceof DOMElement) {
			$this->node = $element;
		}
		else if(is_string($element)) {
			// TODO: New feature: Allow passing in CSS selector to create
			// the element, i.e. create("div.product.selected");
			$this->node = $this->_dom->getDomDoc()->createElement(
				$element, $value);
		}

		if(is_array($attrArray)) {
			foreach($attrArray as $key => $value) {
				$this->node->setAttribute($key, $value);
			}
		}
	}

	public function offsetExists($selector) {
		
	}

	public function offsetGet($selector) {
		return $this->_dom->offsetGet($selector, $this->node);
	}

	public function offsetSet($selector, $value) {
		// TODO: Does this need to be implemented?
	}

	public function offsetUnset($selector) {
		// TODO: Remove item's children matching the selector.
	}

	/**
	* TODO: Docs.
	*/
	public function append($toAppend) {
		$elementArray = array();

		if(is_array($toAppend) || $toAppend instanceof DomElCollection) {
			$elementArray = $toAppend;
		}
		else {
			$elementArray[] = $toAppend;
		}

		foreach($elementArray as $element) {
			$elNode = $element;
			if($element instanceof DomEl) {
				$elNode = $element->node;
			}

			$this->node->appendChild($elNode);
		}
	}

	/**
	* TODO: Docs.
	*/
	public function remove() {
		$this->node->parentNode->removeChild($this->node);
	}

	/**
	 * TODO: Docs.
	 */
	public function cloneNode($deep = true) {
		return new DomEl($this->_dom, $this->node->cloneNode($deep));
	}

	/**
	* TODO: Docs.
	*/
	public function __call($name, $args = array()) {
		if(method_exists($this->node, $name)) {
			return call_user_func_array(array($this->node, $name), $args);
		}
		else {
			return false;
		}
	}

	/**
	* TODO: Docs.
	*/
	public function __get($key) {
		switch($key) {
		case "innerHTML":
		case "innerHtml":
		case "innerText":
			return $this->node->nodeValue;
			break;
		default: 
			if(property_exists($this->node, $key)) {
			// Attempt to never pass a native DOMElement without converting to
			// DomEl wrapper class.
			if($this->node->$key instanceof DOMELement) {
			return $this->_dom->create($this->node->$key);
			}
			return $this->node->$key;
			}
			break;
		}
	}

	/**
	* TODO: Docs.
	*/
	public function __set($key, $value) {
		switch($key) {
		case "innerHTML":
		case "innerHtml":
		case "innerText":
			$this->node->nodeValue = $value;
			break;
		default:
			$this->node->setAttribute($key, $value);
			break;
		}

		$this->updateDom();
	}

	/**
	* TODO: Docs.
	*/
	private function updateDom() {
		$this->_dom->update();
	}
}
?>