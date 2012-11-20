<?php abstract class PageTool {
/**
 * PageTool objects operate in a similar way to PageCode objects. PageTools can
 * be seen as groupings of reusable code, that is made accessible to all
 * PageCode. 
 *
 * A PageTool is usually created when code within a PageCode becomes required
 * elsewhere in an application. This code is then packaged into a tool, that
 * serves a particular purpose.
 *
 * Common tools are provided with PHP.Gt, but application specific tools can
 * be used to keep the PageCode clean.
 */
protected $name = null;
protected $_api = null;
protected $_dom = null;
protected $_template = null;
protected $_tool = null;

public function __construct($api, $dom, $template, $tool) {
	$className = get_class();
	$this->_name = substr($className, 0, strrpos($className, "_PageTool"));
	$this->_api = $api;
	$this->_dom = $dom;
	$this->_template = $template;
	$this->_tool = $tool;
}

/**
 * Calls an API key on the internal API of the current PageTool.
 */
public function query($queryName, $params = array()) {
	return $this->_api[$this->_name]->$queryName($params);
}

/**
 * Works in the same way that PageCode's go() function does.
 * @param ApiWrapper $api Used exactly like the $api variable from within
 * PageCode, but with access to this tool's TableCollections.
 * @param Dom $dom An extended DomDocument, providing helpful functions and
 * most notably element CSS selection. Any manipulation that is made to the
 * DOM will be sent to the browser.
 * @param array $template An associative array containing all DOM elements
 * that have been scraped out of the DOM with data-template attributes.
 * Each element keeps its designed form from how it appears in the HMTL.
 * @param ToolWrapper $tool The object that acts as a single entry point
 * to all PageTools. Can activate a PageTool by calling it through this
 * Associative array.
 */
abstract protected function go($api, $dom, $template, $tool);

}?>