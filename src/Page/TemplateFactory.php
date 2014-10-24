<?php
/**
 *
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Page;

class TemplateFactory {

private $list = [];

/**
 * @param The dom document to scrape template elements from
 */
public function __construct($document) {
	$templateAttribute = $document->config->template_element_attribute;
	$elementList = $document->xpath(".//*[@$templateAttribute]");

	foreach ($elementList as $element) {
		$name = $element->getAttribute($templateAttribute);
		$this->list[$name] = $element;

		$element->templateParentNode = $element->parentNode;
		$element->templateNextSibling = $element->nextSibling;
		$element->templatePreviousSibling = $element->nextSibling;
		$element->remove();
	}
}

public function get($name) {

}

}#