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

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();

	if($cacheInvalid) {
		$clientSideCompiler = new ClientSideCompiler();
		$fileOrganiser->clean();
		$fileOrganiser->update();
		$fileOrganiser->process($clientSideCompiler);
	}

	foreach ($fileContents as $subPath => $contents) {
		$filePath = "$wwwDir/$subPath";
		// $filePath still points to .scss file.
		$this->assertFileNotExists($filePath);

		$filePath = preg_replace("/.scss$/i", ".css", $filePath);
		$actualContents = file_get_contents($filePath);

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

		$fileOrganiser->compile($clientSideCompiler, $domHead, true, true);
	}
	
	$scriptFile = "$wwwDir/Script.js";
	$this->assertFileExists($scriptFile);
	$actualFileContentsCompiled = file_get_contents($scriptFile);
	$actualFileContentsCompiled = trim($actualFileContentsCompiled);

	$this->assertEquals($compiledOutput, $actualFileContentsCompiled);
}

}#