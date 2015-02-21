<?php
/**
 *
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Page;

class TemplateFactory {

public $elementArray = [];
private $templateNodeMap = [
	"parent" => [],
	"nextSibling" => [],
	"previousSibling" => [],
];
private $document;

private static $singleton;

public static function init($document) {
	if(empty(self::$singleton)) {
		self::$singleton = new TemplateFactory($document);
	}

	return self::$singleton;
}

/**
 * @param The dom document to scrape template elements from
 */
public function __construct($document) {
	$this->document = $document;
	$templateAttribute = $document->config->template_element_attribute;
	$elementList = $document->xpath(".//*[@$templateAttribute]");

	foreach ($elementList as $element) {
		$name = $element->getAttribute($templateAttribute);
		$this->elementArray[$name] = $element;

		$this->templateNodeMap["parent"][$name] = $element->parentNode;
		$this->templateNodeMap["nextSibling"][$name] = $element->nextSibling;
		$this->templateNodeMap["previousSibling"][$name] =
			$element->previousSibling;

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
		$node = $this->elementArray[$name]->cloneNode(true);
		$node->templateName = $name;
		$node->templateParentNode = $this->templateNodeMap["parent"][$name];
		$node->templatePreviousSibling =
			$this->templateNodeMap["previousSibling"][$name];
		$node->templateNextSibling =
			$this->templateNodeMap["nextSibling"][$name];

		return $node;
	}

	return null;
}

}#