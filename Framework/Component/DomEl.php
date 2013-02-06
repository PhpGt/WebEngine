<?php class DomEl implements ArrayAccess {
/**
* A wrapper to PHP's native DOMElement, adding more object oriented
* features to be more like JavaScript's implementation.
*/

public $node;
private $_dom;

// Helps debugging:
private $_tagName;
private $_class;
private $_id;
private $_contents;

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
public function append($toAdd) {
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

	return $toAdd;
}

/**
 * TODO: Docs.
 */
public function prepend($toAdd) {
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

	return $toAdd;
}

/**
 * TODO: Docs.
 */
public function before($toAdd) {
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
}

public function after($toAdd) {
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
* TODO: Docs.
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
}

/**
 * TODO: Docs.
 */
public function removeChildren() {
	while($this->node->hasChildNodes()) {
		$this->node->removeChild($this->node->lastChild);
	}
}

/**
 * TODO: Docs.
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
 * TODO: Docs.
 */
public function insertBefore($toInsert) {
	return $this->insert($toInsert, "before");
}

/**
 * TODO: Docs.
 */
public function insertAfter($toInsert) {
	return $this->insert($toInsert, "after");
}

/**
 * TODO: Docs.
 */
private function insert($toInsert, $direction) {
	$elementArray = array();

	if(is_array($toInsert) || $toInsert instanceof DomElCollection) {
		$elementArray = $toInsert;
	}
	else if(is_string($toInsert)) {
		$attrArray = null;
		$value = null;
		$args = func_get_args();
		if(isset($args[1])) {
			$attrArray = $args[1];
		}
		if(isset($args[2])) {
			$value = $args[2];
		}

		$elementArray[] = new DomEl(
			$this->_dom,
			$toInsert,
			$attrArray,
			$value
		);
	}
	else {
		$elementArray[] = $toInsert;
	}

	foreach($elementArray as $element) {
		$elNode = $element;
		if($element instanceof DomEl) {
			$elNode = $element->node;
		}

		if(strtolower($direction) == "before") {
			$inserted = $this->node->parentNode->insertBefore(
				$elNode, $this->node);
		}
		else {
			$nextSibling = $this->node->nextSibling;
			if(is_null($nextSibling)) {
				$this->node->parentNode->appendChild($elNode);
			}
			else {
				$this->node->parentNode->insertBefore(
					$elNode, $nextSibling);
			}
		}
	}
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
public function addClass($className) {
	$stringArray = array();

	if(is_array($className)) {
		$stringArray = $className;
	}
	else {
		$stringArray = array($className);
	}

	$currentClass = $this->node->getAttribute("class");

	foreach($stringArray as $string) {
		if(!$this->hasClass($string)) {
			$currentClass .= " " . $string;
		}
	}

	$this->node->setAttribute("class", $currentClass);
}

/**
 * TODO: Docs.
 */
public function removeClass($className) {
	$stringArray = array();

	if(is_array($className)) {
		$stringArray = $className;
	}
	else {
		$stringArray = array($className);
	}

	$currentClass = $this->node->getAttribute("class");

	foreach($stringArray as $string) {
		// Remove any occurence of the string, with optional spaces.
		$currentClass = preg_replace(
			"/\b" . $string . "\b/",
			"",
			$currentClass);
	}

	$this->node->setAttribute("class", $currentClass);
}

/**
 * TODO: Docs.
 */
public function hasClass($className) {
	if(!$this->node instanceof DOMElement) {
		return false;
	}
	$stringArray = array();

	if(is_array($className)) {
		$stringArray = $className;
	}
	else {
		$stringArray = array($className);
	}

	$currentClass = $this->node->getAttribute("class");

	foreach($stringArray as $string) {
		if(preg_match("/\b" . $string . "\b/", $currentClass)) {
			return true;
		}
	}

	return false;
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
* TODO: Docs.
*/
public function __call($name, $args = array()) {
	if(method_exists($this->node, $name)) {
		$result = null;
		try {
			$result = call_user_func_array(
				array($this->node, $name),
				$args
			);
		}
		catch(Exception $e) {
			// TODO: Throw proper error.
			var_dump(xdebug_get_function_stack());
			var_dump($name, $args);die();
		}
		return $result;
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
	case "text":
		return $this->node->nodeValue;
		break;
	default:
		if(property_exists($this->node, $key)) {
			// Attempt to never pass a native DOMElement without converting
			// to DomEl wrapper class.
			if($this->node->$key instanceof DOMNode) {
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
* TODO: Docs.
*/
public function __set($key, $value) {
	switch($key) {
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

}?>