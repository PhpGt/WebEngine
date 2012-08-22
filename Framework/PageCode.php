<?php abstract class PageCode {
/**
 * PageCode objects are where all user code is executed. This provides a single
 * entry point for each page request's code, and exposes various wrappers 
 * utilising dependency injection so that any required code can be executed or
 * triggered from within the PageCode.
 */
private $_stop;

protected $_api = null;
protected $_dom = null;
protected $_template = null;
protected $_tool = null;

public function __construct(&$stop) {
	$this->_stop = &$stop;
}

/**
 * Internal function for setting the protected variables (also used internally).
 */
public function setVars($api, $dom, $template, $tool) {
	$this->_api = $api;
	$this->_dom = $dom;
	$this->_template = $template;
	$this->_tool = $tool;
}

/**
 * The main function that contains all data IO and DOM manipulation.
 * @param ApiWrapper $api The object that acts as a single entry point to
 * all database manipulation. Acts as a dependency injector.
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

/**
 * Calling this function will ensure that no more PageCode instances are
 * executed in the current request. This could be used to disable execution
 * of _Common.php PageCodes, or from within _Common.php itself where it will
 * stop the execution of nested PageCodes in directories above the cwd.
 */
protected function stop() {
	$this->_stop = true;
}

}?>