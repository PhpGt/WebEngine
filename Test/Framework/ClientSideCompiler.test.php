<?php class ClientSideCompilerTest extends PHPUnit_Framework_TestCase {

public function setup() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/FileOrganiser.php");
	require_once(GTROOT . "/Framework/ClientSideCompiler.php");
}

public function tearDown() {
	removeTestApp();
}

/**
 * Test that SCSS files are converted to CSS, and the source SCSS file is not
 * present in www directory after the processing. The "compiled" css that is
 * hard-coded here will be stripped of all white-space when comparing to the 
 * actual white-space.
 */
public function testScssIsProcessed() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Style/TestVar.scss" => [
			"Source" => "\$red = #fa124e; * { color: \$red; }",
			"Compiled" => "* { color: #fa124e; }"],
		"Style/TestNest.scss" => [
			"Source" => "p { color: red; a { color: blue; } }",
			"Compiled" => "p { color: red; } p a { color: blue; }"],
		"Style/SubDir/TestMixin.scss" => [
			"Source" => "@mixin Test() { color: red; } p { @include Test(); }",
			"Compiled" => "p { color: red; }"],
	);
	foreach ($fileContents as $subPath => $contents) {
		$dir = dirname(APPROOT . "/$subPath");
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents(APPROOT . "/$subPath", $contents["Source"]);
	}

	$clientSideCompiler = new ClientSideCompiler();

	foreach ($fileContents as $subPath => $contents) {
		$clientSideCompiler->process(APPROOT . "/$subPath");

		$filePath = preg_replace("/\.scss$/i", ".css", $subPath);
		// $filePath now points to .css file.
		$this->assertFileExists(APPROOT . "/$filePath");

		$actualContents = file_get_contents(APPROOT . "/$filePath");

		$actual_stripped = preg_replace('/\s+/', '', $actualContents);
		$compiled_stripped = preg_replace('/\s+/', '', $contents["Compiled"]);

		$this->assertEquals($actual_stripped, $compiled_stripped);
	}
}

/**
 * Test that when isClientCompiled is set, the www directory files get compiled
 * into minified and obfuscated scripts.
 */
public function testClientSideCombination() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Style/Style1.css" => "* { color: red; }",
		"Style/Style2.css" => "p { color: blue; }",
		"Style/SubDir/Style3.css" => "p a { color: black; }",

		"Script/Script1.js" => "var test = 'This is ';",
		"Script/Script2.js" => "test += 'a test!';",
		"Script/SubDir/Script3.js" => "alert(test);",
	);

	$html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="/Style/Style1.css" />
	<link rel="stylesheet" href="/Style/Style2.css" />
	<link rel="stylesheet" href="/Style/SubDir/Style3.css" />

	<script src="/Script/Script1.js"></script>
	<script src="/Script/Script2.js"></script>
	<script src="/Script/SubDir/Script3.js"></script>
</head>
<body>
	<h1>Test</h1>
</body>
</html>
HTML;

	$fileContentsCombined = array();
	foreach ($fileContents as $subPath => $contents) {
		$dir = dirname(APPROOT . "/$subPath");
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents(APPROOT . "/$subPath", $contents);

		$type = substr($subPath, 0, strpos($subPath, "/"));
		if(!isset($fileContentsCombined[$type])) {
			$fileContentsCombined[$type] = "";
		}
		$fileContentsCombined[$type] .= $contents . "\n";
	}

	$dom = new Dom($html);

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();

	if($cacheInvalid) {
		$clientSideCompiler = new ClientSideCompiler();
		$fileOrganiser->clean();
		$fileOrganiser->update();

		$domHead = $dom["head"];

		// Force combination by passing true. You can also force compilation by
		// passing another true into the method.
		$fileOrganiser->compile($clientSideCompiler, $domHead, true);
	}

	$this->assertFileExists("$wwwDir/Script.js");
	$actualFileContentsCombined = file_get_contents("$wwwDir/Script.js");
	$actualFileContentsCombined = preg_replace('/\s+/', '', 
		$actualFileContentsCombined);
	$fileContentsCombined["Script"] = preg_replace('/\s+/', '', 
		$fileContentsCombined["Script"]);
	$this->assertEquals($actualFileContentsCombined, 
		$fileContentsCombined["Script"]);

	$this->assertFileExists("$wwwDir/Style.css");
	$actualFileContentsCombined = file_get_contents("$wwwDir/Style.css");
	$actualFileContentsCombined = preg_replace('/\s+/', '', 
		$actualFileContentsCombined);
	$fileContentsCombined["Style"] = preg_replace('/\s+/', '', 
		$fileContentsCombined["Style"]);
	$this->assertEquals($actualFileContentsCombined, 
		$fileContentsCombined["Style"]);

	$domHeadScriptTags = $dom["head > script"];
	$domHeadStyleTags = $dom["head > link"];

	$this->assertCount(1, $domHeadScriptTags);
	$this->assertCount(1, $domHeadStyleTags);
	$this->assertEquals("/Script.js", $domHeadScriptTags[0]->src);
	$this->assertEquals("/Style.css", $domHeadStyleTags[0]->href);
}

public function testClientSideCompilation() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Script/Script1.js" => "var test = 'This is ';",
		"Script/Script2.js" => "test += 'a test!';",
		"Script/SubDir/Script3.js" => "alert(test);",
	);

	$html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="/Style/Style1.css" />
	<link rel="stylesheet" href="/Style/Style2.css" />
	<link rel="stylesheet" href="/Style/SubDir/Style3.css" />

	<script src="/Script/Script1.js"></script>
	<script src="/Script/Script2.js"></script>
	<script src="/Script/SubDir/Script3.js"></script>
</head>
<body>
	<h1>Test</h1>
</body>
</html>
HTML;

	$compiledOutput = 'var test="This is ",test=test+"a test!";alert(test);';
	foreach ($fileContents as $subPath => $contents) {
		$dir = dirname(APPROOT . "/$subPath");
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents(APPROOT . "/$subPath", $contents);
	}

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();

	if($cacheInvalid) {
		$dom = new Dom($html);

		$clientSideCompiler = new ClientSideCompiler();
		$fileOrganiser->clean();
		$fileOrganiser->update();

		$domHead = $dom["head"][0];

		// The last 2 parameters force combination and compilation respectfully.
		$fileOrganiser->compile($clientSideCompiler, $domHead, true, true);
	}
	
	$scriptFile = "$wwwDir/Script.js";
	$this->assertFileExists($scriptFile);
	$actualFileContentsCompiled = file_get_contents($scriptFile);
	$actualFileContentsCompiled = trim($actualFileContentsCompiled);

	$this->assertEquals($compiledOutput, $actualFileContentsCompiled);
}

/**
 * Test that client side files added to the DOM head by PageTools are handled
 * in the correct way, and compiled as standard.
 */
public function testPageToolClientSideCompiled() {
	$wwwDir = APPROOT . "/www";
	$html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="/Style/TheOnlyStyle.css" />
	<script src="/Script/TheOnlyScript.js"></script>
</head>
<body>
	<h1>Test</h1>
</body>
</html>
HTML;
	$pageToolPhp = <<<PHP
<?php class TestPT_PageTool extends PageTool {
public function go(\$api, \$dom, \$template, \$tool) {
	\$this->clientSide();
}
}#
PHP;
	$pageToolJs = "msg = msg + 'This is from PageTool';";
	$pageToolCss = "a { color: green; }";

	$styleFile = APPROOT . "/Style/TheOnlyStyle.css";
	if(!is_dir(dirname($styleFile))) {
		mkdir(dirname($styleFile), 0775, true);
	}
	$css = "p { color: red; }";
	file_put_contents($styleFile, $css);

	$scriptFile = APPROOT . "/Script/TheOnlyScript.js";
	if(!is_dir(dirname($scriptFile))) {
		mkdir(dirname($scriptFile), 0775, true);
	}
	$js = "var msg = 'This is the only script';alert(msg);";
	file_put_contents($scriptFile, $js);

	$pageToolPhpFile = APPROOT . "/PageTool/TestPT/TestPT.tool.php";
	if(!is_dir(dirname($pageToolPhpFile))) {
		mkdir(dirname($pageToolPhpFile), 0775, true);
	}
	file_put_contents($pageToolPhpFile, $pageToolPhp);

	$pageToolScriptFile = APPROOT . "/PageTool/TestPT/Script/TestPT.tool.js";
	if(!is_dir(dirname($pageToolScriptFile))) {
		mkdir(dirname($pageToolScriptFile), 0775, true);
	}
	file_put_contents($pageToolScriptFile, $pageToolJs);

	$pageToolStyleFile = APPROOT . "/PageTool/TestPT/Style/TestPT.tool.css";
	if(!is_dir(dirname($pageToolStyleFile))) {
		mkdir(dirname($pageToolStyleFile), 0775, true);
	}
	file_put_contents($pageToolStyleFile, $pageToolCss);

	require_once(GTROOT . "/Framework/EmptyObject.php");
	require_once(GTROOT . "/Framework/Component/PageToolWrapper.php");
	require_once(GTROOT . "/Framework/PageTool.php");
	$emptyObject = new EmptyObject();
	$dom = new Dom($html);
	$tool = new PageToolWrapper($emptyObject, $dom, $emptyObject);

	$fileOrganiser = new FileOrganiser();
	$clientSideCompiler = new ClientSideCompiler();
	$cacheInvalid = $fileOrganiser->checkFiles();

	if($cacheInvalid) {
		$fileOrganiser->clean();
	}
	// Call the PageTool as it would be done in the app.
	// Within the go method, the clientSide() method is called.
	$testPT = $tool["TestPT"];
	$testPT->go($emptyObject, $dom, $emptyObject, $emptyObject);

	if($cacheInvalid) {
		$fileOrganiser->update();
		$domHead = $dom["head"][0];
		$fileOrganiser->compile($clientSideCompiler, $domHead, true, true);
	}

	$combinedScriptFile = "$wwwDir/Script.js";
	$combinedScript = file_get_contents($combinedScriptFile);
	$combinedScript = preg_replace('/\s+/', '', $combinedScript);
	$combinedStyleFile = "$wwwDir/Style.css";
	$combinedStyle = file_get_contents($combinedStyleFile);
	$combinedStyle = preg_replace('/\s+/', '', $combinedStyle);

	$expectedScript = 
		'varmsg="Thisistheonlyscript";alert(msg);msg+="ThisisfromPageTool";';
	$expectedStyle = "p{color:red;}a{color:green;}";

	$this->assertEquals($expectedScript, $combinedScript);
	$this->assertEquals($expectedStyle, $combinedStyle);
}

/**
 * Tests that the //= require syntax performs a server-side include of 
 * JavaScript asset files.
 */
public function testJavaScriptRequire() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Script/Script1.js" => "//= require /Script/Script2.js\n"
								. "//= require /Script/SubDir/Script3.js\n"
								."alert(test);",
		"Script/Script2.js" => "test = 'this is a test';",
		"Script/SubDir/Script3.js" => "test += ', appended.';",
	);

	$html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<script src="/Script/Script1.js"></script>
</head>
<body>
	<h1>Test</h1>
</body>
</html>
HTML;

	// Create the source files.
	foreach ($fileContents as $subPath => $contents) {
		$dir = dirname(APPROOT . "/$subPath");
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents(APPROOT . "/$subPath", $contents);
	}

	$dom = new Dom($html);
	$fileOrganiser = new FileOrganiser();
	$clientSideCompiler = new ClientSideCompiler();
	$fileOrganiser->clean();
	$fileOrganiser->update();
	$domHead = $dom["head"][0];
	$fileOrganiser->processHead($domHead, $clientSideCompiler);

	// At this point, the 'required' JavaScript files should be appended to
	// the DOM head, before the requirer.
	$scriptElements = $dom["head > script"];
	$this->assertEquals(3, $scriptElements->length);

	// Because script 1 requires scripts 2 then 3, the order should be:
	$hrefOrder = array("2", "3", "1");
	foreach ($scriptElements as $i => $scriptElement) {
		$this->assertStringEndsWith(
			$hrefOrder[$i] . ".js", 
			$scriptElement->src);
	}
}

/**
 * Tests that the //= require_tree syntax performs a recursive server-side
 * include of JavaScript asset files within the given directory.
 */
public function testJavaScriptRequireTree() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Script/Main.js" => "//= require_tree /Script/Namespace/\n"
							. "//= require_tree /Script/Go/\n",
		"Script/Namespace/Test/Functions.js"=>";namespace('Test.Functions', {\n"
			. "sayPage: function(msg) {\n"
			. "    alert('You are on page: ' + window.location.href);\n"
			. "    if(msg) {\n"
			. "        alert('Message: ' + msg);\n"
			. "    }\n"
			. "\n}"
			. "});",
		"Script/Go/Index.js" => ";go(function() {\n"
			. "Test.Functions.sayPage();\n"
			. "});",
		"Script/Go/Test.js" => ";go(function() {\n"
			. "Test.Functions.sayPage('TEST!');\n"
			. "});",
	);

	$html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<script src="/Script/Main.js"></script>
</head>
<body>
	<h1>Test</h1>
</body>
</html>
HTML;

	// Create the source files.
	foreach ($fileContents as $subPath => $contents) {
		$dir = dirname(APPROOT . "/$subPath");
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		file_put_contents(APPROOT . "/$subPath", $contents);
	}

	$dom = new Dom($html);
	$fileOrganiser = new FileOrganiser();
	$clientSideCompiler = new ClientSideCompiler();
	$fileOrganiser->clean();
	$fileOrganiser->update();
	$domHead = $dom["head"][0];
	$fileOrganiser->processHead($domHead, $clientSideCompiler);

	// At this point, the 'required' JavaScript files should be appended to
	// the DOM head, before the requirer.
	$scriptElements = $dom["head > script"];
	$this->assertEquals(4, $scriptElements->length);

	// require_tree doesn't infer an order.
	$sourceList = array("Main", "Functions", "Index", "Test");
	foreach ($scriptElements as $i => $scriptElement) {
		$src = $scriptElement->src;
		$src = substr($src, strrpos($src, "/") + 1);
		$src = substr($src, 0, strrpos($src, ".js"));
		$this->assertContains($src,	$sourceList);
	}
}

}#