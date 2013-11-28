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
	createTestApp();
	require_once(GTROOT . "/Framework/FileOrganiser.php");
	require_once(GTROOT . "/Framework/Manifest.php");
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
	$fileOrganiser->organiseManifest();

	// Because of the forced organisation, cache muse be valid!
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Change a single file, then cache must be false!
	reset($styleContents);
	$styleFile = key($styleContents);
	$styleFileToChange = APPROOT . "/Style/" . $styleFile;
	$newContents = "* { color: blue; }";
	file_put_contents($styleFileToChange, $newContents);
	
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertFalse($cacheValid, "Cache should be invalid");

	// Re-evaluate the cache again.
	$fileOrganiser->organiseManifest();
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Remove a reference of a file within .manifest file.
	// (now it doesn't reference Main.js).
	$scriptManifest = "SubDir/Script.js";
	file_put_contents($scriptManifestPath, $scriptManifest);
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertFalse($cacheValid, "Cache should be invalid");

	// Re-evaluate the cache again.
	$fileOrganiser->organiseManifest();
	$cacheValid = $fileOrganiser->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$this->assertTrue($cacheValid, "Cache should be valid");

	// Remove a source file, expect an exception.
	unlink($styleFileToChange);
	$caughtException = false;
	try {
		$cacheValid = $fileOrganiser->checkCache(
			FileOrganiser::CACHETYPE_MANIFEST);		
	}
	catch(Exception $e) {
		$caughtException = true;
	}

	$this->assertTrue($caughtException, "Caught exception");
}

}#