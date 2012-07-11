<?php abstract class PageTool {
/**
 * A tool is a device that is used to undertake a particular job. The jobs that
 * need tools are usually repetative, and can be solved generically.
 * The need of a PageTool comes from seeing repetative or extensive tasks being
 * carried out in the PageCode object.
 * Common tools are provided with PHP.Gt, but application specific tools can
 * be used to keep the PageCode clean.
 */
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

}?>