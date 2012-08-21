<?php class Dom implements ArrayAccess {
private $_domDoc = null;

/**
* Dom is a wrapper to PHP's native DOMDocument which adds many features to
* boost development efficiency. The most notable feature is allowing
* manipulation via CSS selectors.
* @param string $html Whole document to parse in HTML format. This should
* only ever be passed in via the Response object by flushing the output
* buffer.
*/
public function __construct($html) {
	// If converting encoding costs too much time, use a simpler method
	// by prepending '<?xml encoding="UTF-8">' to $html.
	$html = mb_convert_encoding($html, "HTML-ENTITIES", "utf-8");
	$this->_domDoc = new DomDocument("1.0", "utf-8");
	libxml_use_internal_errors(true);
	if(!$this->_domDoc->loadHTML($html) ) {
		// TODO: Throw and log a proper error.
		die("Error loading HTML into Dom");
	}

	// Add the url and file to the body's id and class attributes.
	$bodyTag = $this->getElementsByTagName("body");

	if($bodyTag->length > 0) {
		$pathId = str_replace("/", "_", DIR);
		if(strlen($pathId) > 0 && strlen(FILE) > 0) {
			$pathId .= "_";
		}
		$pathId .= FILE;
		$classArray = explode("/", DIR . "/" . FILE);

		foreach($classArray as $class) {
			$bodyTag->addClass(lcfirst($class));
		}
		$bodyTag->setAttribute("id", strtolower($pathId));
	}
}

/**
 * Automatic calling of non-existent methods. Methods that are not explicitly
 * defined within this class will be caught here. Common uses of this will
 * be to call a method that is part of the DOMDocument itself, such as
 * saveHTML() or getElementById()
 * @param string $name The method's name
 * @param array $args An array of arguments passed to the method.
 * @return mixed Returns the outcome of calling the method with given args.
 */
public function __call($name, $args) {
	// If a missing method is called, attempt to call it on the DOMDocument.
	if(method_exists($this->_domDoc, $name)) {
		// Attempt to only pass PHP.Gt DomEl and DomElCollections.
		$result = call_user_func_array(
			array($this->_domDoc, $name),
			$args);
		if($result instanceof DOMElement) {
			return new DomEl($this->_domDoc, $result);
		}
		if($result instanceof DOMNodeList) {
			return new DomElCollection($this->_domDoc, $result);
		}

		return $result;
	}
} 

/**
* Checks to see if a given CSS selector exists matches any DOM Elements.
* Can be used via isset() or empty().
* @param string $selector CSS selector to check.
* @return bool Whether the CSS selector matches any DOM Elements.
*/
public function offsetExists($selector) {
	$collection = $this[$selector];
	return $collection->length > 0;
}

/**
* Returns an array of Dom elements (type DomEl) that match the 
* provided CSS selector.
* @param string $selector CSS selector to match.
* @param DOMNode|DomEl $contextNode Optional. The sub-node to query.
* @return array An array of matching DomEl objects.
*/
public function offsetGet($selector, $contextNode = null) {
	if(!is_null($contextNode)) {
		if($contextNode instanceof DomEl) {
			$contextNode = $contextNode->node;
		}
	}

	// Working with an XQuery to CSS convertion utility:
	$xQuery = new CssXpath_Utility($selector);
	$xpath = new DOMXPath($this->_domDoc);

	// Remove double slash if a context is given.
	if(!is_null($contextNode)) {
		if(strpos($xQuery, "//") === 0) {
			//$xQuery = str_replace("//", "*", $xQuery);
			//$xQuery = substr($xQuery, 2);
			$orig = $xQuery . "";
			$xQuery = preg_replace("/^\/\//", ".//", $xQuery);
			$xQuery = preg_replace("/\|\/\//", "|.//", $xQuery);
		}
	}

	$domNodeList = $xpath->query($xQuery, $contextNode);

	return new DomElCollection($this, $domNodeList);
}

/**
* Replaces zero or more DOM Elements that match the given CSS selector with
* another DOM Element.
* @param string $selector CSS selector describing element(s) to replace.
* @param DomEl|DomElCollection $value The element to replace with.
* @return DomElCollection The elements collection that was replaced.
*/
public function offsetSet($selector, $value) {
	$current = $this[$selector];
	$current->before($value);
	$current->remove();
	
	return $current;
}

/**
* Removes a given CSS selector from the DOM. If more than one element
* matches the given selector, all matches will be removed.
* @param string $selector CSS selector describing element(s) to remove.
* @return DomElCollection The element(s) that were removed.
*/
public function offsetUnset($selector) {
	$current = $this[$selector];
	$current->remove();
	return $current;
}

public function getDomDoc() {
	return $this->_domDoc;
}

/**
* Wrapper to create a new DomEl object while keeping reference to the DOM.
* @param string|DomEl|DomElement The new element to create.
* @param array $attrArray Optional. An associative array of attributes to
* assign to the newly created DomEl.
* @param string $value Optional. The initial text value of the element.
* @return DomEl The newly created DomEl object.
*/
public function create(
$el,
$attrArray  = null,
$value      = null) {
	return new DomEl($this, $el, $attrArray, $value);
}

/**
* Outputs the HTML contained within the DOM to the output buffer and
* instantly flushes the buffer to the browser.
*/
public function flush() {
	ob_clean();
	$this->_domDoc->formatOutput = true;
	echo $this->_domDoc->saveHTML();
	ob_flush();
}

/**
* Searches the DOM for elements with the template attribute. All elements
* get removed from the DOM and stored in an associative array.
* @return array An array of DomEl objects that have their template attribute
* values as the array keys.
*/
public function template($attribute = "data-template") {
	$xpath = new DOMXPath($this->_domDoc);
	$domNodeList = $xpath->query("//*[@{$attribute}]");
	$domNodeListLength = $domNodeList->length;

	$domNodeArray = array();

	for($i = 0; $i < $domNodeListLength; $i++) {
		$item = $domNodeList->item($i);
		$domNodeArray[$item->getAttribute($attribute)] = 
			new DomEl($this, $item);
	}

	$domNodeCollection = new DomElCollection($this, $domNodeArray);

	$domNodeCollection->remove();
	return $domNodeCollection;
}

public function templateOutput($templateWrapper) {
	$domNodeCollection = new DomElCollection(
		$this, $templateWrapper->getArray());
	// Remove the collection from the DOM (wherever it may be) and place it
	// into a DIV with id of PHPGt_Template_Elements for picking up in
	// Gt.js.
	$body = $this->_domDoc->getElementsByTagName("body");
	if($body->length > 0) {
		$body = new DomEl($this, $body->item(0));

		$templateDiv = new DomEl(
			$this,
			"div",
			array(
				"id"	=> "PHPGt_Template_Elements",
				"style"	=> "display: none;"
			)
		);
		$templateDiv->append($domNodeCollection->cloneNodes());
		$body->append($templateDiv);
	}
	
	$domNodeCollection->map(function(&$element, $key, $c_attribute) {
		$element->removeAttribute($c_attribute);
	}, $attribute);
}

/**
 * Loops over all elements in current DOM. Those who's attribute value
 * matches a key in the $data object will have their value set to the 
 * value of the data key.
 * @param array|DalResult $data The dataset to use.
 * @param string $elementSelector Optional. The CSS selector to match on
 * elements.
 * @param strong $attr Optional. The name of the attribute to look for.
 * @return int The number of affected elements.
 */
public function mapData($data, $elementSelector = "*", $attr = "name") {
	$count = 0;
	$domNodes = $this[$elementSelector];
	foreach ($domNodes as $node) {
		if(isset($data[$node->$attr])) {
			$node->value = $data[$node->$attr];
			$count++;
		}
	}
	return $count;
}

}?>