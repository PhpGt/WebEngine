<?php class ClientSideCompilerTest extends PHPUnit_Framework_TestCase {

public function setup() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/Manifest.php");
	require_once(GTROOT . "/Framework/FileOrganiser.php");
	require_once(GTROOT . "/Framework/ClientSideCompiler.php");
}

public function tearDown() {
	removeTestApp();
}

/**
 * Loads an .scss file and makes sure that it processes to proper CSS.
 */
public function testSassProcesses() {
	$scss = "\$myColour = rgba(red, 0.8);
		body { h1 { background: \$myColour; } }";
	$scssFile = APPROOT . "/Style/Main.scss";
	if(!is_dir(dirname($scssFile))) {
		mkdir(dirname($scssFile), 0775, true);
	}
	file_put_contents($scssFile, $scss);

	$processed = ClientSideCompiler::process($scssFile, $scssFile . ".css");
	$this->assertContains("body h1", $processed["Contents"]);
}


/**
 * Test the various "//= require" syntax within JavaScript files:
 * From https://github.com/BrightFlair/PHP.Gt/issues/111
 *
 * //= require /Script/Lib/jquery.js
 * //= require_tree /Script/Go/
 * //= require_tree /Script/Namespace/
 */
public function testJavaScriptRequires() {
	$js = [
		"/Script/Main.js" => "alert('Just a test, from Main.js');
			//= require Relative/Path/Include.js
			alert('Is everything working?');
			//= require /Script/IncludeAbsolute.js
			alert('One last requirement...');
			//= require_tree /Script/Inc",
		"/Script/Relative/Path/Include.js" => "alert('testing from relative');",
		"/Script/IncludeAbsolute.js" => "alert('absolute');",
		"/Script/Inc/1.js" => "alert('First inc');",
		"/Script/Inc/2.js" => "alert('Second inc');",
	];
	foreach ($js as $file => $contents) {
		$filePath = APPROOT . $file;
		if(!is_dir(dirname($filePath))) {
			mkdir(dirname($filePath), 0775, true);
		}
		file_put_contents($filePath, $contents);
	}

	$processed = ClientSideCompiler::process(APPROOT . "/Script/Main.js",
		"Output.js");
	$processedContents = $processed["Contents"];
	$processedContents = preg_replace("/\s/", "", $processedContents);

	$expected = "";
	foreach ($js as $file => $contents) {
		$lines = explode("\n", $contents);
		foreach ($lines as $l) {
			if(strstr($l, "//=")) {
				continue;
			}
			$expected .= preg_replace("/\s/", "", $l) . "\n";
		}
	}

	$expectedLines = explode("\n", $expected);
	foreach ($expectedLines as $l) {
		if(empty($l)) {
			continue;
		}
		$this->assertContains($l, $processedContents);
	}
}

}#