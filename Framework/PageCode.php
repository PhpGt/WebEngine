<?php
/**
 * TODO: Docs.
 */
abstract class PageCode {
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
}
?>