<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Page;

use \Gt\Dom\Document;

class TemplateFactory_Test extends \PHPUnit_Framework_TestCase {

private $config;

public function setUp() {
	$this->config = new \Gt\Core\ConfigObj([
		"template_element_attribute" => "data-template",
	]);
}

public function testTemplatesInHead() {
	$html = "<!doctype html>"
		. "<head>"
		.	"<meta charset='utf-8'/>"
		.	"<link rel='unit-test' href='#' data-template='testLink'/>"
		. "</head>"
		. "<body>"
		.	"<h1>Hello, PHPUnit!</h1>"
		. "</body>";
	$document = new Document($html);
	$document->config = $this->config;

	$template = new TemplateFactory($document);
	$this->assertCount(1, $template->elementArray);
}

public function testTemplatesInBody() {
	$html = "<!doctype html>"
		. "<head>"
		.	"<meta charset='utf-8'/>"
		. "</head>"
		. "<body>"
		.	"<ul id='test-list'>"
		.		"<li data-template='test-item'>Test list item!</li>"
		.	"</ul>"
		. "</body>";
	$document = new Document($html);
	$document->config = $this->config;

	$template = new TemplateFactory($document);
	$this->assertCount(1, $template->elementArray);
}

public function testMultipleTemplates() {
	$html = "<!doctype html>"
		. "<head>"
		.	"<meta charset='utf-8'/>"
		. "</head>"
		. "<body>"
		.	"<ul id='test-list'>"
		.		"<li data-template='test-item'>Test list item!</li>"
		.	"</ul>"
		.	"<table>"
		.		"<tr data-template='test-row'>"
		.			"<td data-template='test-cell'>Test cell!</td>"
		.		"</tr>"
		.	"</table>"
		. "</body>";
	$document = new Document($html);
	$document->config = $this->config;

	$template = new TemplateFactory($document);
	$this->assertCount(3, $template->elementArray);
}

public function testConfigurableAttribute() {
	$html = "<!doctype html>"
		. "<meta charset='utf-8'/>"
		. "<ul id='test-list'>"
		.	"<li template='test-item'>Test list item!</li>"
		. "</ul>";
	$document = new Document($html);
	$document->config = new \Gt\Core\ConfigObj([
		"template_element_attribute" => "template",
	]);

	$template = new TemplateFactory($document);
	$this->assertCount(1, $template->elementArray);
	$element = $template->get("test-item");
	$this->assertInstanceOf("\Gt\Dom\Node", $element, 'Should be a node');
	$this->assertEquals("Test list item!", $element->textContent);
}

public function testTemplatesAreRemoved() {
	$html = "<!doctype html>"
		. "<meta charset='utf-8'/>"
		. "<ul id='test-list'>"
		.	"<li data-template='test-item'>Test list item!</li>"
		. "</ul>";
	$document = new Document($html);
	$document->config = $this->config;

	$template = new TemplateFactory($document);
	$nodeList = $document->querySelectorAll("ul#test-list>li");
	$this->assertCount(0, $nodeList);
}

public function testTemplateGetter() {
	$html = "<!doctype html>"
		. "<meta charset='utf-8'/>"
		. "<ul id='test-list'>"
		.	"<li data-template='test-item'>Test list item!</li>"
		. "</ul>";
	$document = new Document($html);
	$document->config = $this->config;

	$template = new TemplateFactory($document);
	$li = $template->get("test-item");

	$this->assertInstanceOf("\Gt\Dom\Node", $li);
	$this->assertEquals("LI", $li->tagName);
	$this->assertEquals("Test list item!", $li->textContent);
}

}#