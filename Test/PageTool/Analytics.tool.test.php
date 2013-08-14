<?php class AnalyticsPageToolTest extends PHPUnit_Framework_TestCase {
private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Analytics Test</title>
</head>
<body>
	<h1>Hello, Analytics!</h1>
</body>
</html>
HTML;

public function setup() {
	require_once(GTROOT . "/Framework/EmptyObject.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/PageTool.php");
	require_once(GTROOT . "/Framework/Component/PageToolWrapper.php");
}

public function tearDown() {
	// removeTestApp();
}


public function testAnalyticsToolExists() {
	$empty = new EmptyObject();
	$tool = new PageToolWrapper($empty, $empty, $empty);
	$analyticsPageTool = $tool["Analytics"];
	
	$this->assertInstanceOf("PageTool", $analyticsPageTool);
	$this->assertInstanceOf("Analytics_PageTool", $analyticsPageTool);
}
/**
 * Description.
 */
public function testCodeIsAddedToHead() {
	$dom = new Dom($this->_html);
	$tool = new PageToolWrapper(new EmptyObject(), $dom, new EmptyObject());

	$tool["Analytics"]->track("123Test");

	$script = $dom["head > script"];

	$this->assertTrue($script->hasAttribute("data-PageTool"));
	$this->assertEquals("Analytics", $script->getAttribute("data-PageTool"));
	$this->assertContains(
		"_gaq.push(['_setAccount', '123Test']);", $script->innerHTML);
}

}#