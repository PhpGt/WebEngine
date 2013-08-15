<?php class NavigationPageToolTest extends PHPUnit_Framework_TestCase {
private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Navigation Test</title>
</head>
<body>
	<h1>Hello, Navigation!</h1>
	<nav>
		<ul>
			<li>
				<a href="/">Home</a>
			</li>
			<li>
				<a href="/Bare.html">Bare</a>
			</li>
			<li>
				<a href="/Nested.html">Nested</a>
			</li>
		</ul>
	</nav>
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


public function testNavigationToolExists() {
	$empty = new EmptyObject();
	$tool = new PageToolWrapper($empty, $empty, $empty);
	$analyticsPageTool = $tool["Navigation"];
	
	$this->assertInstanceOf("PageTool", $analyticsPageTool);
	$this->assertInstanceOf("Navigation_PageTool", $analyticsPageTool);
}

public function testHomepageSelected() {
	$empty = new EmptyObject();
	$dom = new Dom($this->_html);
	$tool = new PageToolWrapper($empty, $dom, $empty);
	$_SERVER['REQUEST_URI'] = "/Index.html";

	$tool->go("Navigation");

	$selected = $dom["nav li.selected"];
	$this->assertNotEmpty($selected);
	$this->assertCount(1, $selected);
	$this->assertEquals("Home", $selected["a"]->textContent);
}

public function testBareSelected() {
	$empty = new EmptyObject();
	$dom = new Dom($this->_html);
	$tool = new PageToolWrapper($empty, $dom, $empty);
	$_SERVER['REQUEST_URI'] = "/Bare.html";

	$tool->go("Navigation");

	$selected = $dom["nav li.selected"];
	$this->assertNotEmpty($selected);
	$this->assertCount(1, $selected);
	$this->assertEquals("Bare", $selected["a"]->textContent);
}

public function testNestedSelected() {
	$empty = new EmptyObject();
	$dom = new Dom($this->_html);
	$tool = new PageToolWrapper($empty, $dom, $empty);
	$_SERVER['REQUEST_URI'] = "/Nested/Page.html";

	$tool->go("Navigation");

	$selected = $dom["nav li.selected"];
	$this->assertNotEmpty($selected);
	$this->assertCount(1, $selected);
	$this->assertEquals("Nested", $selected["a"]->textContent);
}

}#