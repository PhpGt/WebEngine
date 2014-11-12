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

public $elementArray = [];

/**
 * @param The dom document to scrape template elements from
 */
public function __construct($document) {
	$templateAttribute = $document->config->template_element_attribute;
	$elementList = $document->xpath(".//*[@$templateAttribute]");

	foreach ($elementList as $element) {
		$name = $element->getAttribute($templateAttribute);
		$this->elementArray[$name] = $element;

		$element->templateParentNode = $element->parentNode;
		$element->templateNextSibling = $element->nextSibling;
		$element->templatePreviousSibling = $element->nextSibling;
		$element->remove();
	}
}

/**
 * Gets a templated element by name, or null if no template is found by the
 * provided name.
 *
 * @param string $name Name of the template - either the `data-template`
 * attribute value, the `id` of the `<template>` element, or the name of the
 * templated html file
 *
 * @return \Gt\Dom\Node|null A clone of the node, or null if no template found
 */
public function get($name) {
	if(isset($this->elementArray[$name])) {
		return $this->elementArray[$name]->cloneNode(true);
	}

	return null;
}

}#