<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {
	private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Manifest Test</title>
	<meta name="manifest" content="TestFileOrganiser" />
</head>
<body>
	<h1>Manifest Test</h1>
</body>
</html>
HTML;

public function setUp() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/FileOrganiser.php");
	require_once(GTROOT . "/Framework/Manifest.php");
	require_once(GTROOT . "/Framework/ClientSideCompiler.php");
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
 * Ensure that when source files change, the FileOrganiser reports back
 * correctly that the manifests' caches are invalid.
 */
public function testCheckCache() {
	$styleManifestPath = APPROOT . "/Style/TestFileOrganiser.manifest";
	$styleManifest = "Main.css";
	$styleContents = ["Main.css" => "* { color: red; }"];

	$scriptManifestPath = APPROOT . "/Script/TestFileOrganiser.manifest";
	$scriptManifest = "Main.js\nSubDir/Script.js";
	$scriptContents = [
		"Main.js" => "* { alert('Hello!') }",
		"SubDir/Script.js" => "Stupid idiot, the above script will fail!",
	];

	if(!is_dir(dirname($styleManifestPath))) {
		mkdir(dirname($styleManifestPath, 0775, true));
	}
	file_put_contents($styleManifestPath, $styleManifest);
	foreach ($styleContents as $fileName => $contents) {
		$fileName = APPROOT . "/Style/$fileName";
		if(!is_dir(dirname($fileName))) {
			mkdir(dirname($fileName), 0775, true);
		}
		file_put_contents($fileName, $contents);
	}

	if(!is_dir(dirname($scriptManifestPath))) {
		mkdir(dirname($scriptManifestPath, 0775, true));
	}
	file_put_contents($scriptManifestPath, $scriptManifest);
	foreach ($scriptContents as $fileName => $contents) {
		$fileName = APPROOT . "/Script/$fileName";
		if(!is_dir(dirname($fileName))) {
			mkdir(dirname($fileName), 0775, true);
		}
		file_put_contents($fileName, $contents);
	}

	$html = $this->_html;
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$fileOrganiser = new FileOrganiser([new Manifest("TestFileOrganiser")]);

	// Force the organisation of www directory for the first time.
	$fileOrganiser->organiseManifest($domHead);

	// Because of the forced organisation, cache muse be valid!
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Change a single file, then cache must be false!
	reset($styleContents);
	$styleFile = key($styleContents);
	$styleFileToChange = APPROOT . "/Style/" . $styleFile;
	$newContents = "* { color: blue; }";
	file_put_contents($styleFileToChange, $newContents);

	$cacheValid = $fileOrganiser->checkCache(
		FileOrganiser::CACHETYPE_MANIFEST, true);
	$this->assertFalse($cacheValid, "Cache should be invalid");

	// Re-evaluate the cache again.
	$fileOrganiser->organiseManifest($domHead);
	$cacheValid = $fileOrganiser->checkCache(
		FileOrganiser::CACHETYPE_MANIFEST, true);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Remove a reference of a file within .manifest file.
	// (now it doesn't reference Main.js).
	$scriptManifest = "SubDir/Script.js";
	file_put_contents($scriptManifestPath, $scriptManifest);
	$cacheValid = $fileOrganiser->checkCache(
		FileOrganiser::CACHETYPE_MANIFEST, true);
	$this->assertFalse($cacheValid, "Cache should be invalid");

	// Re-evaluate the cache again.
	$fileOrganiser->organiseManifest($domHead);
	$cacheValid = $fileOrganiser->checkCache(
		FileOrganiser::CACHETYPE_MANIFEST, true);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Remove a source file, expect an exception.
	unlink($styleFileToChange);
	$caughtException = false;
	try {
		$cacheValid = $fileOrganiser->checkCache(
			FileOrganiser::CACHETYPE_MANIFEST, true);		
	}
	catch(Exception $e) {
		$caughtException = true;
	}

	$this->assertTrue($caughtException, "Caught exception");
}

/**
 * Ensures that manifests are purely optional.
 */
public function testFileOrganiserNoManifest() {
	$sourceFiles = array(
		"Script" => [
			"Main.js" =>
				"alert('From main.js!');"
		],
		"Style" => [
			"Main.css" => 
				"body { background: red; }",
		],
	);
	foreach ($sourceFiles as $type => $file) {
		foreach ($file as $fileName => $contents) {
			$filePath = APPROOT . "/$type/$fileName";

			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}
			file_put_contents($filePath, $contents);
		}
	}

	$htmlNoManifest = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>FileOrganiser Test</title>
	<link rel="stylesheet" href="/Style/Main.css" />
	<script src="/Script/Main.js"></script>
</head>
<body>
	<h1>FileOrganiser Test</h1>
</body>
</html>
HTML;
	
	$dom = new Dom($htmlNoManifest);
	$domHead = $dom["html > head"][0];
	$manifestList = Manifest::getList($domHead);
	$fileOrganiser = new FileOrganiser($manifestList);
	$fileOrganiser->organise($domHead);

	// The two client-side files should exist in www/Script and www/Style
	// seeing as there is no named manifest declared.
	foreach ($sourceFiles as $type => $file) {
		foreach ($file as $fileName => $contents) {
			$filePath = APPROOT . "/www/$type/$fileName";
			$filePathSource = APPROOT . "/$type/$fileName";

			$this->assertFileExists($filePath);
			$this->assertFileEquals($filePathSource, $filePath);
		}
	}
}

/**
 * Ensure that scss files are processed into css, and that javascript files
 * expand server-side requirements.
 */
public function testClientSideProcessing() {
	$sourceFiles = array(
		"Style" => [
			"Main.scss" => 
				"\$red = #fd2376;
				@include SecondStyle",
			"SecondStyle.scss" => 
				"body { background \$red; }",
		],
	);

	foreach ($sourceFiles as $type => $file) {
		foreach ($file as $fileName => $contents) {
			$filePath = APPROOT . "/$type/$fileName";

			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}
			file_put_contents($filePath, $contents);
		}
	}

	$htmlNoManifest = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>FileOrganiser Test</title>
	<link rel="stylesheet" href="/Style/Main.scss" />
</head>
<body>
	<h1>FileOrganiser Test</h1>
</body>
</html>
HTML;
	
	$dom = new Dom($htmlNoManifest);
	$domHead = $dom["html > head"][0];
	$manifestList = Manifest::getList($domHead);
	$fileOrganiser = new FileOrganiser($manifestList);
	$fileOrganiser->organise($domHead);

	// The link in the head should be renamed to css.
	$linkEl = $domHead["link"][0];
	$this->assertInstanceOf("DomEl", $linkEl);
	$this->assertEquals("/Style/Main.css", $linkEl->getAttribute("href"));
}

}#