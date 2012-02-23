<?php
/**
 * A tool is a device that is used to undertake a particular job. The jobs that
 * need tools are usually repetative, and can be solved generically.
 * The need of a PageTool comes from seeing repetative or extensive tasks being
 * carried out in the PageCode object.
 * Common tools are provided with PHP.Gt, but application specific tools can
 * be used to keep the PageCode clean.
 */
abstract class PageTool {
	protected $_api = null;
	protected $_dom = null;
	protected $_template = null;
	protected $_tool = null;
	
	public function __construct($api, $dom, $template, $tool) {
		$this->_api = $api;
		$this->_dom = $dom;
		$this->_template = $template;
		$this->_tool = $tool;
	}

	// Force PageTools to implement the main function with these parameters.
	abstract protected function go($api, $dom, $template, $tool);

	/**
	 * Methods that are private/protected *could* be accessed via this method,
	 * but may be bad style...
	 * TODO: Decide what style to stick with.
	 */
	/*
	public function __call($name, $args) {
		if(method_exists($this, $name)) {
			return call_user_func_array(
				array($this, $name),
				array_merge(
					array($this->_api,
						  $this->_dom,
						  $this->_template,
						  $this->_tool),
					$args
			));
		}
		else {
			// TODO: Throw exception.
		}
	}
	*/
}
?>