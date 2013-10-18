<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	createTestApp();
	require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/FileOrganiser.php");
}

public function tearDown() {
	removeTestApp();
}

/**
 * There should only be one file within the www directory in a brand new app:
 * the Go.php, which is triggered by the webserver.
 */
public function testInitialWebrootIsEmpty() {
	$webroot = APPROOT . "/www";
	$diff = array_diff(["Go.php"], scandir($webroot));
	$this->assertEmpty($diff, "Unexpected www directory contents");
}

/**
 * Test that cache is invalid when the www.cache file hasn't been made.
 */
public function testCheckFilesNew() {
	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();

	$this->assertTrue($cacheInvalid, "Cache should be invalid.");
}

/**
 * Test that the cache is valid after FileOrganiser's methods have been called.
 * This tests that PHP.Gt places all required files into the www directory that
 * it should.
 */
public function testCheckFilesWhenCached() {
	file_put_contents(APPROOT . "/Asset/SomeAssetData.dat", "Asset contents");
	file_put_contents(APPROOT . "/Script/Main.js", "alert('Script!')");
	file_put_contents(APPROOT . "/Style/Main.css", "* { color: red; }");

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();
	$this->assertTrue($cacheInvalid);

	if($cacheInvalid) {
		$fileOrganiser->clean();
		$fileOrganiser->update();
	}

	$cacheInvalid = $fileOrganiser->checkFiles();
	$this->assertFalse($cacheInvalid);
}

/**
 * Test that the files in the source directories are actually copied to the
 * www directory.
 */
public function testFilesAreCopied() {
	$wwwDir = APPROOT . "/www";
	$fileContents = array(
		"Asset/SomeAssetData.dat" => "Asset content",
		"Script/Main.js" => "alert('Script!')",
		"Style/Main.css" => "* { color: red; }",
	);
	foreach ($fileContents as $subPath => $contents) {
		file_put_contents(APPROOT . "/$subPath", $contents);
	}

	$fileOrganiser = new FileOrganiser();
	$cacheInvalid = $fileOrganiser->checkFiles();

	if($cacheInvalid) {
		$fileOrganiser->clean();
		$fileOrganiser->update();
	}

	foreach ($fileContents as $subPath => $contents) {
		$filePath = "$wwwDir/$subPath";
		$this->assertFileExists($filePath);
		$actualContents = file_get_contents($filePath);
		$this->assertEquals($contents, $actualContents);
	}
}

/**
 * Test that client side files added to the DOM head by PageTools are handled
 * in the correct way.
 */
public function testPageToolClientSide() {
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
	$pageToolJs = "alert('This is from PageTool')";
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
	$js = "alert('This is from the only script');";
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

	// Perform the basic actions usually performed by the Dispatcher object:
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
		$fileOrganiser->compile($clientSideCompiler, $domHead, true, false);
	}

	$combinedScriptFile = "$wwwDir/Script.js";
	$combinedScript = file_get_contents($combinedScriptFile);
	$combinedScript = preg_replace('/\s+/', '', $combinedScript);
	$combinedStyleFile = "$wwwDir/Style.css";
	$combinedStyle = file_get_contents($combinedStyleFile);
	$combinedStyle = preg_replace('/\s+/', '', $combinedStyle);

	$expectedScript = preg_replace('/\s+/', '', $js)
		. preg_replace('/\s+/', '', $pageToolJs);
	$expectedStyle = preg_replace('/\s+/', '', $css)
		. preg_replace('/\s+/', '', $pageToolCss);

	$this->assertEquals($expectedScript, $combinedScript);
	$this->assertEquals($expectedStyle, $combinedStyle);
}

/**
 * Test that when source files are overwritten, it causes a cache invalidation,
 * and they are re-coppied to the www directory.
 */
public function testNewFilesCauseInvalid() {
	file_put_contents(APPROOT . "/Asset/SomeAssetData.dat", "Asset contents");
	file_put_contents(APPROOT . "/Script/Main.js", "alert('Script!')");
	file_put_contents(APPROOT . "/Style/Main.css", "* { color: red; }");

	$fileOrganiser = new FileOrganiser();
	$this->assertTrue($fileOrganiser->checkFiles());

	$fileOrganiser->clean();
	$fileOrganiser->update();

	$cacheInvalid = $fileOrganiser->checkFiles();
	$this->assertFalse($cacheInvalid);

	// Make a change to one of the files:
	file_put_contents(APPROOT . "/Asset/SomeAssetData.dat", "New contents!");
	$this->assertTrue($fileOrganiser->checkFiles());

	$fileOrganiser->clean();
	$fileOrganiser->update();

	// Add another file:
	file_put_contents(APPROOT . "/Script/Second.js", "location.reload()");
	$this->assertTrue($fileOrganiser->checkFiles());
}

}#