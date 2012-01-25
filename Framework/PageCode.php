<?php
/**
 * TODO: Docs.
 */
abstract class PageCode {
	$this->_tools = null;

	public function __construct() { 
		//  What initialisation is needed here?
	}

	/**
	 * Must be called from the PageCode's init function. Declares what PageTools
	 * are being used for this page. PageTools are reusable modules of code that
	 * would be repetative to place in the PageCode. Examples of popular tools:
	 * Content, Blog, Gallery.
	 * @param array|string $toolName A single tool name, or an array of tool
	 * names to assign for use within this page.
	 */
	private function assignTool($toolName) {
		if(is_null($this->_tools)) {
			$this->_tools = array();
		}

		if(is_array($toolName)) {
			array_merge($this->_tools, $toolName);
		}
		else if(is_string($toolName)) {
			$this->_tools[] = $toolName;
		}
		else {
			// TODO: Throw error.
			die("Invalid PageTool assigned.");
		}
	}

	/**
	 * Called by the dispatcher to obtain a list of all PageTools assigned.
	 * PageTools assigned after init function will be ignored.
	 */
	public function getTools() {
		return $this->_tools;
	}

	/**
	 * Called first, the init function should declare what modules to use in the
	 * page. Modules will have full access to API and DOM, so highly interactive
	 * pages can have some logic split between app-specific module files.
	 */
	abstract protected function init();

	/**
	* When an HTTP POST request is made, this function is called before any
	* others in the PageCode class. POSTed data can be manipulated here.
	*/
	abstract protected function onPost();

	/**
	* When an HTTP GET request is made, this function is called before any
	* others in the PageCode class. GET data can be manipulated here.
	*/
	abstract protected function onGet();

	/**
	* Where the majority of all calculations are made and logic is executed
	* for this page. Access to the APIs and manipulation/storage of data should
	* be done at this stage.
	* @param Api $api The Api object that is used as a wrapper to the database
	* access layer, adding functionality and/or data manipulation.
	*/
	abstract protected function main($api);

	/**
	* Called just before flushing the output buffer in the response. DOM
	* manipulation is performed here, and DOM elements scraped from the HTML
	* are accessible here. Any API usage should have been done in main(), and
	* stored the data in private variables.
	* @param Dom $dom The Dom object inherits from PHP's DOMDocument, giving it
	* added functionality. Manipulations are updated before being sent to the
	* browser.
	* @param array $templates An associative array of DomElements, which have
	* already been scraped from the DOM. To allow for structuring dynamic 
	* content within the HTML, any nodes with data-phpgt attributes will be
	* collected for manipulation, and the values of the data-phpgt attributes
	* will be used for the array keys.
	* @param Injector $injector Object that holds the "injected" items such as
	* compiled <script> and <link> elements.
	*/
	abstract protected function render($dom, $templates, $injector);
}
?>