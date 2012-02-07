<?php
/**
 * TODO: Docs.
 */
abstract class PageCode {
	/**
	 * Called first, the init function should declare what tools to use in the
	 * page. Tools will have full access to API and DOM, so highly interactive
	 * pages can have some logic split between app-specific tool classes.
	 *
	 * This function is optional - it is only needed to be present in each
	 * PageCode that requires access to tools, or requires some initialisation
	 * logic to be performed.
	 * @param ToolWrapper $tool A dependency injector to the PageTool objects.
	 */
	protected function init($tool){}

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
	 */
	abstract protected function go($api, $dom, $template);
}
?>