<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {

public function setUp() {
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
 * There should only be one file within the www directory in a brand new app:
 * the Go.php, which is triggered by the webserver.
 */
public function testInitialWebrootIsEmpty() {
	$webroot = APPROOT . "/www";
	$diff = array_diff(["Go.php"], scandir($webroot));
	$this->assertEmpty($diff, "Unexpected www directory contents");
}

/**
 * Takes all files represented by the manifest, processes them using the 
 * ClientSideCompiler, and writes them to the manifest's fingerprint directory,
 * only if the manifest's cache is invalid.
 */
public function testProcessCopy() {
	$approotFileDetails = [
		"/Style/Main.css" => 
			"body {
				background: black;
			}",
	];
	ManifestTest::putApprootFile($approotFileDetails);
	$manifest = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/Main.css",],
	]);

	$fileOrganiser = new FileOrganiser($manifest);
	$fileOrganiser->organise();
	// Get actual public path from dom head, ensure corresponding file exists.
	$domHead = $manifest->getDomHead();
	$linkElement = $domHead["link[rel='stylesheet']"];
	$wwwSource = $linkElement->href;
	$this->assertNotEquals(key($approotFileDetails), $wwwSource);
	$wwwFullPath = APPROOT . "/www" . $wwwSource;
	$this->assertFileExists($wwwFullPath);

	// Same test, this time with SCSS.
	removeTestApp();
	createTestApp();

	ManifestTest::putApprootFile([
		"/Style/Main.scss" => 
			"body {
				> h1 {
					color: red;
				}
			}",
	]);
	$manifest = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/Main.scss",],
	]);

	$fileOrganiser = new FileOrganiser($manifest);
	$fileOrganiser->organise();
	// Get actual public path from dom head, ensure corresponding file exists.
	// This time, ensure the path is to a processed css file, not scss.
	$domHead = $manifest->getDomHead();
	$linkElement = $domHead["link[rel='stylesheet']"];
	$wwwSource = $linkElement->href;
	$this->assertNotEquals(key($approotFileDetails), $wwwSource);
	$this->assertNotRegexp("/\.scss$/", $wwwSource);
	$wwwFullPath = APPROOT . "/www" . $wwwSource;
	$this->assertFileExists($wwwFullPath);
}

/**
 * When a source file changes it should invalidate and flush all caches in the
 * www directory.
 */
public function testCacheInvalidates() {
	ManifestTest::putApprootFile([
		"/Style/Main.scss" => 
			"body {
				> h1 {
					color: red;
				}
			}",
	]);
	$manifest = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/Main.scss",],
	]);

	$this->assertFalse($manifest->isCacheValid());

	$fileOrganiser = new FileOrganiser($manifest);
	$fileOrganiser->organise();

	$this->assertTrue($manifest->isCacheValid());
	
}

/**
 * Should take all pre-processed files in the www/fingerprint directory, 
 * then combine and minify them into a single file, then remove the originals.
 */
public function testMinifyClean() {

}

/**
 * All non-css files should be copied into the www/Style directory recursively,
 * and a cachefile should be used to represent the entire contents of the 
 * style directory. 
 */
public function testCopyStyleFiles() {

}

/**
 * If any files change in either APPROOT or GTROOT's Style
 * directory, this should cause the StyleFiles cache to be invalid, and the
 * StyleFiles cache along with all fingerprint directories should be removed.
 */
public function testStyleFileModificationRemovesAllCaches() {

}

/**
 * APPROOT/Asset directory should be copied to the www/Asset directory, an 
 * Asset cache should be created. If the Asset cache is valid, copying should
 * be skipped.
 */
public function testCopyAsset() {

}

}#