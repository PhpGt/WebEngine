<?php
class DomElCollection implements Iterator, ArrayAccess {
	private $_dom;
	private $_elArray;
	private $_index;

	/**
	* Stores a collection of DomEl objects, accessible as an inexed array.
	* @param array $elArray An array containing either DomEl objects or PHP's
	* native DOMElement objects, that will be automatically converted into
	* DomEl objects.
	*/
	public function __construct($dom, $elArray = array()) {
		$this->_dom = $dom;

		if(!is_array($elArray)) {
			// Possible to only pass a single DOMElement or DomEl object as param.
			if($elArray instanceof DOMElement) {
				$elArray = array($dom->create($elArray));
			}
			else if($elArray instanceof DomEl) {
				$elArray = array($elArray);
			}
			else if($elArray instanceof DOMNodeList) {
				$list = $elArray;
				$listLength = $elArray->length;

				$elArray = array();
				for($i = 0; $i < $listLength; $i++) {
					$elArray[] = new DomEl($dom, $list->item($i));
				}
			}
			else {
				var_dump($elArray);
				// TODO: Proper error logging and output.
				die("Error creating DomElCollection.");
			}
		}

		$this->_elArray= $elArray;
		$this->_index = 0;
	}

	public function rewind() {
		$this->_index = 0;
	}

	public function valid() {	
		return isset($this->_elArray[$this->_index]);
	}

	public function current() {
		return $this->_elArray[$this->_index];
	}

	public function key() {
		return $this->_index;
	}

	public function next() {
		++$this->_index;
	}

	/**
	* Calls the given function on each DomEl in the stored element array.
	* @return mixed The result of calling the function on the last element in
	* the collection.
	*/
	public function __call($name, $args) {
		$result = null;
		foreach($this->_elArray as $el) {
			if(is_callable(array($el, $name)) ) {
				$result = call_user_func_array(array($el, $name), $args);
			}
		}

		return $result;
	}

	/**
	* Returns the requested property from the first contained element. This
	* allows for a more natrual coding style when using CSS selectors to work
	* with selectors only matching one element i.e. $dom["p#main"]->innerText
	* @param string $key The property name to retrieve.
	* @return mixed The value of the requested property.
	*/
	public function __get($key) {
		$key = strtolower($key);

		switch($key) {
		case "length":
			$this->checkElementsInDom();
			return count($this->_elArray);
			break;
		default:
			if(count($this->_elArray) < 1) {
				// TODO: Properly log and throw error.
				die("Error: DomElCollection is empty.");
				return;
			}
			
			return $this->_elArray[0]->$key;
			break;
		}
	}

	/**
	* Sets the property named $key of elements within the collection with the 
	* provided value.
	* @param string $key The property to set.
	* @param mixed $value The value to assign to the given property.
	*/
	public function __set($key, $value) {
		foreach($this->_elArray as $el) {
			$el->$key = $value;
		}
	}

	/**
	 * TODO: Docs.
	 * Arbitary number of params to send into scope of callback.
	 */
	public function map($callback, $callbackArg = null) {
		array_walk($this->_elArray, $callback, $callbackArg);
	}

	/**
	 * TODO: Docs.
	 * @return array
	 */
	public function getElArray() {
		return $this->_elArray;
	}

	/**
	 * Cloning a DomElCollection returns a brand new instance with clones of the
	 * DomEl objects.
	 * TODO: Docs.
	 */
	public function cloneNodes($deep = true) {
		$elArray = array();
		foreach($this->_elArray as $el) {
			$elArray[] = $el->cloneNode($deep);
		}

		return new DomElCollection($this->_dom, $elArray);
	}

	/**
	 * TODO: Docs. Makes sure all elements are still present in DOM.
	 */
	public function checkElementsInDom() {
		foreach ($this->_elArray as $key => $el) {
			if(is_null($el->node->parentNode)) {
				unset($this->_elArray[$key]);
			}
		}

		$this->_elArray = array_values($this->_elArray);
	}

	/**
	 * Randomises the order of the element's children.
	 */
	public function shuffle() {
		$this->checkElementsInDom();

		// Save reference to parent node, while the link exists.
		$parentNode = $this->_elArray[0]->parentNode;
		
		// Remove them all from the DOM.
		foreach ($this->_elArray as $el) {
			$el->remove();
		}

		shuffle($this->_elArray);
		// Add them back into the DOM, in their shuffled order.
		foreach ($this->_elArray as $el) {
			$parentNode->append($el);
		}
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetExists($index) {
		// Must check that offset hasn't been removed from DOM.
		if(array_key_exists($index, $this->_elArray)) {
			// No parent node? Not in DOM any more!
			if(is_null($this->_elArray[$index]->parentNode)) {
				// Remove from array, reset indices.
				unset($this->_elArray[$index]);
				$this->_elArray = array_values($this->_elArray);
			}
			return array_key_exists($index, $this->_elArray);
		}
		else {
			return false;
		}
	} 

	/**
	 * TODO: Docs.
	 */
	public function offsetGet($index) {
		if($this->offsetExists($index)) {
			return $this->_elArray[$index];
		}
		else {
			return null;
		}
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetSet($index, $value) {
		die("TODO: OffsetSet not yet implemented");
		// TODO: Implement offsetSet:
		// Replace element[0] with given element, remove others.
		// OR, if a collection is given, replace it with that instead.
		return 0;
	}


	/**
	 * TODO: Docs.
	 */
	public function offsetUnset($index) {
		// TODO: Implement offsetUnset... see above TODOs.
		return 0;
	}
}
?>