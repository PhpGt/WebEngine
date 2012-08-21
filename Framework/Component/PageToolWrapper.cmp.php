<?php class PageToolWrapper implements ArrayAccess {
/**
 * Can access as an array - that gets to the raw object - but for simplicity,
 * you should call $tool->go("ToolName"); The function "go" can be anything that
 * exists in the PageTool object.
 */
private $_api = null;
private $_dom = null;
private $_template = null;

private $_pageToolObjects = array();

public function __construct($api, $dom, $template) {
	$this->_api = $api;
	$this->_dom = $dom;
	$this->_template = $template;
}

public function offsetExists($offset) {
	$offset = ucfirst($offset);
	return isset($this->_pageToolObjects[$offset]);
}

public function offsetGet($offset) {
	$offset = ucfirst($offset);
	if($this->offsetExists($offset)) {
		return $this->_pageToolObjects[$offset];
	}
	else {
		// Attempt to find and load the required tool.
		
		$pathArray = array(
			APPROOT . DS . "PageTool" . DS . $offset . DS,
			GTROOT  . DS . "PageTool" . DS . $offset . DS
		);
		$fileName = $offset . ".tool.php";
		$className = $offset . "_PageTool";
		foreach ($pathArray as $path) {
			if(is_dir($path)) {
				if(file_exists($path . $fileName)) {
					require_once($path . $fileName);
					if(class_exists($className)) {
						$this->_pageToolObjects[$offset] = new $className(
							$this->_api,
							$this->_dom,
							$this->_template,
							$this
						);
						return $this->_pageToolObjects[$offset];
					}
					else {
						// TODO: Throw proper error.
						die("PageTool $offset found but couldn't load.");
					}
				}
			}
		}
	}

	// TODO: Throw error here.
	return null;
}

public function offsetSet($offset, $value) {
	// TODO: More appropriate error message and logging.
	die("What are you setting the PageTool for???");
}

public function offsetUnset($offset) {
	// TODO: More appropriate error message and logging.
	die("What are you unsetting the PageTool for???");
}

/** 
 * Maps the PageToolWrapper's method to the PageTool object matching the
 * given name. For example, $tool->go("Foo") will call the "go" method
 * of the Foo PageTool, injecting the PHPGt arguments. More arguments can
 * be passed to the method by simply supplying them to the function 
 * e.g. $tool->go("Foo", $bar, $baz)
 */

public function __call($name, $args) {
	$toolName = $args[0];
	array_shift($args);

	return call_user_func_array(
		array($this[$toolName], $name),
		array_merge(
			array($this->_api, $this->_dom, $this->_template, $this),
			$args
		)
	);
}

}?>