<?php class TemplateTest extends PHPUnit_Framework_TestCase {
private $_htmlDataTemplate = <<<HTML
		<!doctype html>
		<ul id="list">
			<li data-template="List-Item">
				Item number <span>3</span>
			</li>
		</ul>
HTML;
private $_htmlTemplateElement = <<<HTML
		<!doctype html>
		<ul id="list">
			<template id="List-Item">
				<li>
					Item number <span>3</span>
				</li>
			</template>
		</ul>
HTML;

public function setup() {
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/TemplateWrapper.php");
	require_once(GTROOT . "/Framework/EmptyObject.php");
}

public function testTemplateRemoved() {
	$dom = new Dom($this->_htmlDataTemplate);
	$dom->template();

	$liElements = $dom["ul#list li"];
	$this->assertEquals(0, $liElements->length);

	$dom = new Dom($this->_htmlTemplateElement);
	$dom->template();

	$liElements = $dom["ul#list li"];
	$this->assertEquals(0, $liElements->length);
}

public function testTemplatesScaped() {
	$dom = new Dom($this->_htmlTemplateElement);
	$templateArray = $dom->template();
	$template = new TemplateWrapper($templateArray);
	$dom->templateOutput($template);

	$scrapeContainer = $dom["#PhpGt_Template_Elements"];
	$this->assertEquals(1, $scrapeContainer->length);
	$this->assertEquals("display: none;", 
		$scrapeContainer->getAttribute("style"));
}

public function testTemplateWrapper() {
	$dom = new Dom($this->_htmlDataTemplate);
	$templateArray = $dom->template();
	$template = new TemplateWrapper($templateArray);

	$ul = $dom["ul#list"];
	for($i = 0; $i < 10; $i ++) {
		$li = $template["List-Item"];
		$li["span"]->textContent = $i;
		$ul->appendChild($li);
	}

	$this->assertEquals(10, $ul["li"]->length);

	$dom = new Dom($this->_htmlTemplateElement);
	$templateArray = $dom->template();
	$template = new TemplateWrapper($templateArray);

	$ul = $dom["ul#list"];
	for($i = 0; $i < 15; $i ++) {
		$li = $template["List-Item"];
		$li["span"]->textContent = $i;
		$ul->appendChild($li);
	}

	$this->assertEquals(15, $ul["li"]->length);
}

}#