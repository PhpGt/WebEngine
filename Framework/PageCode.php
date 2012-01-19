<?php
abstract class PageCode {
	public function __construct() { 
		// TODO: What initialisation is needed here?
	}

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
	* Called before any DOM elements are templated, so DOM at this stage
	* represents the original HTML perfectly. Any API usage should have been
	* done in main(), where preRender() and render() can access the result from
	* $this->_data. ??? TODO: Confirm this functionality.
	* @param Dom $dom The Dom object inherits from PHP's DOMDocument, giving it
	* added functionality. Manipulations are updated before being sent to the
	* browser.
	*/
	abstract protected function preRender($dom);

	/**
	* Called just before flushing the output buffer in the response. DOM
	* manipulation is performed here, and DOM elements scraped from the HTML
	* are accessible here. Any API usage should have been done in main(),
	* where preRender() and render() can access the result from $this->_data.
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