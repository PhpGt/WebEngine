<?php class DomElClassList {
/**
 * Represents a list of classNames on a particular DomEl, emulating
 * DOM level 2's Element.classList property.
 * @param [type] $className [description]
 */

private $_classArray = array();
private $_domEl = null;

public function __construct($domEl) {
	$this->_domEl = $domEl;
}

private function rebuildArray() {
	$this->_classArray = explode(" ", 
		trim($this->_domEl->getAttribute("class")));
}
private function rebuildClassName() {
	$this->_domEl->setAttribute("class", implode(" ", $this->_classArray));
}

public function add($className1 /*, $className2, ... */) {
	$this->rebuildArray();

	$classNameArray = func_get_args();
	foreach ($classNameArray as $className) {
		$this->_classArray[] = $className;
	}

	// Avoid multiple classes appearing twice.
	$this->_classArray = array_unique($this->_classArray);

	$this->rebuildClassName();
}

public function remove($className1 /*, $className2, ... */) {
	$this->rebuildArray();

	$classNameArray = func_get_args();
	foreach ($classNameArray as $className) {
		$key = array_search($className, $this->_classArray);
		if($key !== false) {
			unset($this->_classArray[$key]);			
		}
	}

	// Re-key the array.
	$this->_classArray = array_values($this->_classArray);

	$this->rebuildClassName();
}

public function toggle($className1 /*, $className2, ... */) {
	$this->rebuildArray();

	$classNameArray = func_get_args();
	foreach ($classNameArray as $className) {
		if($this->contains($className)) {
			$this->remove($className);
		}
		else {
			$this->add($className);
		}
	}

	$this->rebuildClassName();
}

public function contains($className1 /*, $className2, ... */) {
	$this->rebuildArray();


	$contains = false;
	$classNameArray = func_get_args();
	foreach ($classNameArray as $className) {
		if(in_array($className, $this->_classArray)) {
			$contains = true;
		}
	}

	return $contains;
}

}#