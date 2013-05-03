<?php class DomElCollection implements Iterator, ArrayAccess {
/**
 * TODO: Docs.
 *
 * @author Greg Bowler <greg.bowler@g105b.com>
 * @since 0.0.1
 */
private $_dom;
private $_elArray;
private $_index;

/**
* Stores a collection of DomEl objects, accessible as an inexed array.
*
* @param Dom $dom The reference to the current Dom object.
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
			throw new HttpError(500, "Error creating DomElCollection.");
			exit;
		}
	}

	$this->_elArray = $elArray;
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
 * Removes a DomEl from the collection by index or by reference.
 * @param int|DomEl $element    Either an integer index or DomEl instance to
 * remove.
 */
public function removeElement($element) {
	if(is_int($element)) {
		unset($this->_elArray[$key]);
	}
	$key = array_search($element, $this->_elArray);
	unset($this->_elArray[$key]);
}

/**
* Calls the given function on each DomEl in the stored element array.
*
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
* 
* @param string $key The property name to retrieve.
*
* @return mixed The value of the requested property.
*/
public function __get($key) {
	switch($key) {
	case "length":
		$this->checkElementsInDom();
		return count($this->_elArray);
		break;
	default:
		if(count($this->_elArray) < 1) {
			// TODO: Properly log and throw error.
			return;
		}
		
		return $this->_elArray[0]->$key;
		break;
	}
}

/**
* Sets the property named $key of elements within the collection with the 
* provided value.
*
* @param string $key The property to set.
* @param mixed $value The value to assign to the given property.
*/
public function __set($key, $value) {
	foreach($this->_elArray as $el) {
		$el->$key = $value;
	}
}

/**
 * Will append the given string to all attributes of given name within the
 * current collection.
 * 
 * @param string $attr The attribute to append to.
 * @param string $str The value to append.
 */
public function appendToAttribute($attr, $str) {
	foreach ($this->_elArray as $el) {
		$attrValue = $el->getAttribute($attr);

		if($el->hasAttribute($attr)) {
			$attrValue = $attrValue . $str;
		}
		else {
			$attrValue = $str;
		}

		$el->setAttribute($attr, $attrValue);
	}
}

/**
 * Applies the given callback to the internal array, passing an array of
 * arguments to send into scope of callback.
 *
 * @param callable $callback The function name or callback reference to
 * apply on the array.
 * @param array $callbackArg An array of parameters to pass to the callback.
 *
 * @return bool True on success, false on failure.
 */
public function map($callback, $callbackArg = null) {
	return array_walk($this->_elArray, $callback, $callbackArg);
}

/**
 * Returns the internal array of elements.
 * ALPHATODO:
 * TODO: Should this be private?
 *
 * @return array The internal array of elements.
 */
public function getElArray() {
	return $this->_elArray;
}

/**
 * Returns a new DomElCollection containing clones of all elements.
 *
 * @param bool $deep Optional. True to clone all children within current
 * DomElCollection, false to ignore children in cloning process.
 *
 * @return DomElCollection A new instance, containing clones of all elements
 * (not referencing originals).
 */
public function cloneNodes($deep = true) {
	$elArray = array();
	foreach($this->_elArray as $el) {
		$elArray[] = $el->cloneNode($deep);
	}

	return new DomElCollection($this->_dom, $elArray);
}

/**
 * Makes sure all elements are still present in DOM, fixes any problems.
 *
 * Using methods such as remove(), or replacing methods with others will
 * lead to the internal array containing elements who are not actually
 * present within the DOM any more. This method iterates over the internal
 * array, removes any elements without a parent, and rebuilds the array's
 * zero-based index.
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
 * Checks if there is a value associated to the given offset within the
 * internal array. This method is usually used internally.
 * 
 * @param int $index The numerical offset to check.
 *
 * @return bool True if the internal array has the specified index.
 */
public function offsetExists($index) {
	// Must check that offset hasn't been removed from DOM.
	if(array_key_exists($index, $this->_elArray)) {
		// No parent node? Not in DOM any more!
		if(is_null($this->_elArray[$index]->parentNode)) {
			// Remove from array, reset indices.
			$this->offsetUnset($index);
		}
		return array_key_exists($index, $this->_elArray);
	}
	else {
		return false;
	}
} 

/**
 * Returns the DomEl at the provided numerical index, or returns the child
 * elements (if any) of the provided CSS selector. For instance:
 * $main = $dom["div#main"]; $childSpans = $main["span"];
 *
 * @param int|string $index The numerical index or CSS selector to retrieve.
 * 
 * @return DomElCollection A new collection of requested element(s).
 */
public function offsetGet($index) {
	if($this->offsetExists($index)) {
		return $this->_elArray[$index];
	}
	else {
		// Return new DomElCollection of elements within current collection.
		if(is_string($index)) {
			$subElements = array();
			foreach ($this->_elArray as $element) {
				$arrayOfMatches = $element[$index]->getElArray(); 
				$subElements = array_merge($subElements, $arrayOfMatches);
			}
			
			$result = new DomElCollection($this->_dom, $subElements);

			return $result;
		}
		return null;
	}
}

/**
 * Replaces contained element(s) with provided element(s).
 * This method should be used via ArrayAccess, typically
 * $dom["#OldElement"] = $newElement
 *
 * @param int|string $index The numerical or associative key for internal 
 * collection.
 * @param DomEl|DomElCollection|array $value The element(s) that will
 * replace the currently contained element(s).
 */
public function offsetSet($index, $value) {		
	// Remove all elements in collection:
	for($i = 0; $i < $elArrayCount; $i++) {
		$this->offsetUnset($i);
	}
	$this->rewind();

	// Create empty array, fill with provided element(s):
	$this->_elArray = array();

	if(is_array($value)) {
		$this->_elArray = $value;
	}
	else if($value instanceof DomEl) {
		$this->_elArray[] = $value;
	}
	else if($value instanceof DomElCollection) {
		foreach ($value as $el) {
			$this->_elArray[] = $el;
		}
	}
}


/**
 * This method should only be used internally. It removes reference to the
 * indices within the internal collection, and re-indexes the array values
 * so there are no missing indices.
 *
 * @param int|string $index The numerical or associative key to remove.
 */
public function offsetUnset($index) {
	unset($this->_elArray[$index]);
	$this->_elArray = array_values($this->_elArray);
}

}#