<?php class DomEl implements ArrayAccess {
/**
 * A wrapper to PHP's native DOMElement. Helper methods have been added, but
 * all methods and properties of the native DOMElement are still accessible.
 */

public $node;
public $_dom;

// Helps debugging:
private $_tagName;
private $_class;
private $_id;
private $_contents;

public $classList;

public function __construct(
$dom,
$element,
$attrArray  = null,
$value      = null) {
	$this->_dom = $dom;

	if($element instanceof DOMNode) {
		$this->node = $element;
	}
	else if(is_string($element)) {
		$this->node = $this->_dom->getDomDoc()->createElement(
			$element, $value);
	}

	if(is_array($attrArray)) {
		foreach($attrArray as $key => $value) {
			$this->node->setAttribute($key, $value);
		}
	}
	else if(is_string($attrArray) && is_null($value)) {
		// This allows a new element to be created by ignoring the attrArray
		// parameter: $dom->create("p", "This is a test");
		$value = $attrArray;
		$attrArray = null;
		$this->text = $value;
	}

	if($this->node instanceof DOMElement) {
		$this->_tagName = $this->node->tagName;
		$this->_class = $this->node->getAttribute("class");
		$this->_id = $this->node->getAttribute("id");
	}
	else {
		$this->_tagName = "TEXTNODE";
	}
	$this->_contents = $this->node->nodeValue;

	$this->classList = new DomElClassList($this);
}

public function offsetExists($selector) {
	
}

public function offsetGet($selector) {
	return $this->_dom->offsetGet($selector, $this->node);
}

public function offsetSet($selector, $value) {
	// TODO: Throw error when trying to set an element.
}

public function offsetUnset($selector) {
	// TODO: Throw error when trying to unset an element.
}

public function css($selector) {
	return $this->offsetGet($selector);
}

public function xpath($query) {
	return $this->_dom->offsetGet($query, $this->node, true);
}

/**
 * Internal function, converts a given input of either a DOMElement, DomEl,
 * DomElCollection or a string into an array of DomElements.
 * Used by append, prepend, before, after functions.
 * @param mixed $input TODO: Docs.
 * @return array TODO.
 */
private function obtainElementArray($input) {
	$elementArray = array();

	if(is_array($input) || $input instanceof DomElCollection) {
		$elementArray = $input;
	}
	else if(is_string($input)) {
		$attrArray = null;
		$value = null;
		$args = func_get_args();
		if(isset($args[1])) {
			if(is_array($args[1])) {
				$attrArray = $args[1];
			}
			else if(is_string($args[1])) {
				$value = $args[1];
			}
		}
		if(isset($args[2])) {
			$value = $args[2];
		}

		$elementArray[] = new DomEl(
			$this->_dom,
			$input,
			$attrArray,
			$value
		);
	}
	else {
		$elementArray[] = $input;
	}

	return $elementArray;
}

/**
* TODO: Docs.
* @return DomEl The appended element.
*/
public function appendChild($child) {
	$elementArray = call_user_func_array(
		array($this, "obtainElementArray"), 
		func_get_args());

	foreach($elementArray as $element) {
		$elNode = $element;
		if($element instanceof DomEl) {
			$elNode = $element->node;
		}

		$this->node->appendChild($elNode);
	}

	return $child;
}
/**
 * Synonym for appendChild.
 */
public function append() {
	return call_user_func_array([$this, "appendChild"], func_get_args());
}

/**
 * TODO: Docs.
 */
public function prependChild($child) {
	$elementArray = call_user_func_array(
		array($this, "obtainElementArray"), 
		func_get_args());

	foreach($elementArray as $element) {
		$elNode = $element;
		if($element instanceof DomEl) {
			$elNode = $element->node;
		}

		$this->node->insertBefore($elNode, $this->node->firstChild);
	}

	return $child;
}

/**
 * Synonym for prependChild.
 */
public function prepend() {
	return call_user_func_array([$this, "prependChild"], func_get_args());
}

/**
 * Inserts the specified node(s) before a the current node.
 */
public function appendChildBefore($newElement /*, $newElement2, ... */) {
	$elementArray = call_user_func_array(
		array($this, "obtainElementArray"), 
		func_get_args());
	
	foreach($elementArray as $element) {
		$elNode = $element;
		if($element instanceof DomEl) {
			$elNode = $element->node;
		}

		$this->node->parentNode->insertBefore($elNode, $this->node);
	}

	return new DomElCollection($elementArray);
}
/**
 * Synonym for appendChildBefore
 */
public function before() {
	return call_user_func_array([$this, "appendChildBefore"], func_get_args());
}

/**
 * Synonym for appendChildBefore
 */
public function prependSibling() {
	return call_user_func_array([$this, "appendChildBefore"], func_get_args());
}

/**
 * Inserts the specified node(s) after the current node.
 */
public function appendChildAfter($newElement /*, $newElement2, ... */) {
	$elementArray = call_user_func_array(
		array($this, "obtainElementArray"), 
		func_get_args());
	
	foreach($elementArray as $element) {
		$elNode = $element;
		if($element instanceof DomEl) {
			$elNode = $element->node;
		}

		$nextSibling = $this->node->nextSibling;
		if(!is_null($nextSibling)) {
			$this->node->parentNode->insertBefore($elNode, $nextSibling);
		}
		else {
			$this->node->parentNode->appendChild($elNode);
		}
	}

	return new DomElCollection($elementArray);
}

/**
 * Synonym for appendChildAfter.
 */
public function after() {
	return call_user_func_array([$this, "appendChildAfter"], func_get_args());
}

/**
 * Synonym for appendChildAfter.
 */
public function appendSibling() {
	return call_user_func_array([$this, "appendChildAfter"], func_get_args());
}

/**
 * Appends multiple elements to this element, taking values from the
 * array passed in. This element will have however many indeces are in the
 * array appended elements.
 * @param mixed $data The array of data to compute, or an enumerable 
 * object.
 * @param mixed $element The element to create and append for each item in
 * the array.
 * @param array $attrArray A key-value-pair of attribute names and array 
 * keys. Each key will be created as an attribute on the new element,
 * the attribute's value will be the value stored in $data's index that
 * matches the value of the $attrArray key.
 * @param string $textKey The index of each $data element to use as the
 * node to append's text value.
 */
public function appendArray($data, $element,
$attrArray = array(), $textKey = null) {
	$elementToCreate = null;

	if($element instanceof DOMNode) {
		$elementToCreate = new DomEl(
			$this->_dom, 
			$element->cloneNode(true));
	}
	else if($element instanceof DomEl) {
		$elementToCreate = $element->cloneNode();
	}
	else if(is_string($element)) {
		$elementToCreate = new DomEl($this->_dom, $element);
	}

	foreach ($data as $item) {
		$clonedElement = $elementToCreate->cloneNode();

		foreach ($attrArray as $key => $value) {
			if(isset($item[$value])) {
				$clonedElement->setAttribute($key, $item[$value]);
			}
		}

		if(!is_null($textKey)) {
			if(isset($item[$textKey])) {
				$clonedElement->innerText = $item[$textKey];
			}
		}

		$this->append($clonedElement);
	}
}

/**
 * Removes the current element from the DOM. Element can still exist as a
 * reference, to be added again later.
 */
public function remove() {
	if(is_null($this->node->parentNode)) {
		// TODO: How can this ever be possible? (It is ... but doesn't seem to
		// break anything if ignored.) NEEDS TEST CASE.
		return;
		echo("Error: Node has no parent!");
		var_dump($this->node->tagName);die();
	}
	$this->node->parentNode->removeChild($this->node);

	return $this;
}

/**
 * Empties the current element of any children.
 */
public function removeChildren() {
	while($this->node->hasChildNodes()) {
		$this->node->removeChild($this->node->lastChild);
	}
	return $this;
}

/**
 * Replaces the current element with another.
 */
public function replace($replaceWith) {
	$element = null;

	if(is_array($replaceWith) || $replaceWith instanceof DomElCollection) {
		// Can only replace one node with another - take 1st node of array.
		$element = $replaceWith[0];
	}
	else if(is_string($replaceWith)) {
		$attrArray = null;
		$value = null;
		$args = func_get_args();
		if(isset($args[1])) {
			$attrArray = $args[1];
		}
		if(isset($args[2])) {
			$value = $args[2];
		}

		$element = new DomEl(
			$this->_dom,
			$replaceWith,
			$attrArray,
			$value
		);
	}
	else {
		$element = $replaceWith;
	}

	$elNode = $element;
	if($element instanceof DomEl) {
		$elNode = $element->node;
	}

	return $this->node->parentNode->replaceChild($elNode, $this->node);		
}

/**
 * Returns a duplicate of the node on which this method was called. By default
 * all children of the node will also be cloned. Pass false as the only param
 * to only clone the root element.
 */
public function cloneNode($deep = true) {
	return new DomEl($this->_dom, $this->node->cloneNode($deep));
}

/**
 * Synonym for classList->add.
 */
public function addClass($className) {
	return $this->classList->add($className);
}

/**
 * Synonym for classList->remove.
 */
public function removeClass($className) {
	return $this->classList->remove($className);
}

/**
 * Synonym for classList->contains.
 */
public function hasClass($className) {
	return $this->classList->contains($className);
}

/**
 * Synonym for classList->toggle.
 */
public function toggleClass($className) {
	return $this->classList->toggle($className);
}

/**
 * Perform a str_replace on an element's attribute, without having to handle the
 * string multiple times.
 * @param string $attr The attribute of the current element to act as the 
 * haystack.
 * @param string $substr The needle to search for.
 * @param string $replacement The string to replace with.
 * @return bool True on success, false on failure.
 */
public function injectAttribute($attr, $substr, $replacement) {
	if(!$this->node->hasAttribute($attr)) {
		return false;
	}
	$value = $this->node->getAttribute($attr);
	$value = str_replace($substr, $replacement, $value);
	$this->node->setAttribute($attr, $value);
	return true;
}

/**
* Allows underlying DOMNode methods to be called on the DomEl object.
*/
public function __call($name, $args = array()) {
	// Convert any $args to Node or NodeList objects.
	foreach ($args as $key => $value) {
		if(is_a($value, "DomEl")) {
			$args[$key] = $value->node;
		}
		if(is_a($value, "DomElCollection")) {
			$args[$key] = $value->nodeList;
		}
	}

	if(method_exists($this->node, $name)) {
		$result = null;
		try {
			$result = call_user_func_array(
				array($this->node, $name),
				$args
			);
		}
		catch(Exception $e) {
			throw new HttpError(500, null, $e);
		}
		return $result;
	}
	else {
		throw new HttpError(500, __CLASS__ . "::$name method doesn't exist");
	}
}

/**
 * Wrapper to underlying DOMNode properties.
 */
public function __get($key) {
	switch($key) {
	case "className":
		$this->node->getAttribute("class");
		break;
	case "innerHTML":
	case "innerHtml":
	case "html":
	case "HTML":
		$innerHtml = "";
		$children = $this->node->childNodes;
		foreach($children as $child) {
			$tempDom = new DOMDocument("1.0", "utf-8");
			$tempDom->appendChild($tempDom->importNode($child, true));
			$innerHtml .= trim($tempDom->saveHTML());
		}
		return html_entity_decode($innerHtml);
		break;
	case "innerText":
	case "textContent":
	case "text":
		return $this->node->nodeValue;
		break;
	default:
		if(property_exists($this->node, $key)) {
			// Attempt to never pass a native DOMElement without converting
			// to DomEl wrapper class.
			if($this->node->$key instanceof DOMNode
			&& !$this->node->$key instanceof DOMDocument) {
				return $this->_dom->create($this->node->$key);
			}
			return $this->node->$key;
		}
		else if($this->node instanceof DOMElement) {
			if($this->node->hasAttribute($key)) {
				return $this->node->getAttribute($key);
			}
		}
		
		return null;
		break;
	}
}

/**
 * Wrapper to underlying DOMNode properties.
 */
public function __set($key, $value) {
	switch($key) {
	case "className":
		$this->node->setAttribute("class", $value);
		break;
	case "innerHTML":
	case "innerHtml":
	case "html":
	case "HTML":
		$value = mb_convert_encoding($value, 'html-entities', 'utf-8');
		// If plain text string is provided, skip generating DOMDocument.
		if($value == strip_tags($value)) {
			$this->node->nodeValue = $value;
			break;
		}
		$tempDom = new DOMDocument("1.0", "utf-8");
		$tempDom->loadHTML($value);
		$root = $tempDom->documentElement;
		$newNode = $this->_dom->importNode($root, true);

		while($this->node->firstChild) {
			$this->node->removeChild($this->node->firstChild);
		}

		// Ensure a DOMNode is given provided.
		if($newNode instanceof DomEl) {
			$newNode = $newNode->node;
		}

		$nodesToAdd = $newNode->firstChild->childNodes;
		for($i = 0; $i < $nodesToAdd->length; $i++) {
			$add = $nodesToAdd->item($i)->cloneNode(true);
			$this->node->appendChild($add);
		}

		$tempDom = null;
		break;
	case "innerText":
	case "textContent":
	case "text":
		$value = htmlentities($value);//, ENT_COMPAT | ENT_HTML401, "UTF-8", true);
		$this->node->nodeValue = $value;
		break;
	case "value":
		// TODO: Document this heavily - major feature.
		// Allows to set the 'value' of a <select> or <textarea>, and it 
		// will automatically select the correct <option> or output the
		// correct innerText.
		$tag = strtolower($this->node->tagName);
		if($tag == "select") {
			$optionList = $this->node->getElementsByTagName("option");
			$optionListLength = $optionList->length;
			for($i = 0; $i < $optionListLength; $i++) {
				$option = $optionList->item($i);
				if($option->getAttribute("value") == $value) {
					$option->setAttribute("selected", "selected");
				}
			}
			break;
		}

		$nodeValueTags = array("h1", "h2", "h3", "h4", "h5", "h6",
			"p", "span", "a", "label", "textarea", "pre", "time");
		if(in_array($tag, $nodeValueTags)) {
			$value = htmlentities($value);
			$this->node->nodeValue = $value;
			break;
		}
		// Fix for setting HTML5 date input:
		if($this->node->getAttribute("type") == "date") {
			if(strstr($value, "/")) {
				$value = str_replace("/", "-", $value);
			}
			$dt_value = new DateTime($value);
			$value = $dt_value->format("Y-m-d");
		}
		$this->node->setAttribute($key, $value);
		break;
	default:
		if($this->node->hasAttribute($key)) {
			$this->node->setAttribute($key, $value);
		}
		break;
	}
}

public function __toString() {
	$classArray = explode(" ", $this->_class);
	$domClass = "";
	$domId = $this->_id;
	foreach ($domClass as $class) {
		if(trim($class) == "") {
			continue;
		}
		$domClass .= "." . $class;
	}
	if(!empty($domId)) {
		$domId = "#" . $domId;
	}
	return "DomEl({$this->_tagName}) [.{$domClass}#{$domId}]";
}

}#
