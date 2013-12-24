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

	$fileOrganiser = new FileOrganiser($manifest);

	$this->assertFalse($manifest->isCacheValid());
	$this->assertFalse($fileOrganiser->isStyleFilesCacheValid());

	$fileOrganiser->organise();

	$this->assertTrue($manifest->isCacheValid());
	$this->assertTrue($manifest->isCacheValid());
	$this->assertTrue($fileOrganiser->isStyleFilesCacheValid());
	$this->assertTrue($fileOrganiser->isStyleFilesCacheValid());

	ManifestTest::putApprootFile([
		"/Style/Main.scss" => 
			"body {
				> h1 {
					color: blue;
				}
			}",
	]);
	$manifest = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/Main.scss",],
	]);
	$fileOrganiser = new FileOrganiser($manifest);
	$this->assertFalse($fileOrganiser->isStyleFilesCacheValid());
	$this->assertFalse($fileOrganiser->isStyleFilesCacheValid());
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
	ManifestTest::putApprootFile([
		"/Style/RedBody.css" => 
			"body#red {
				background: red;
			}",
		"/Style/BlueBody.css" =>
			"body#blue {
				background: blue;
			}"
	]);
	$manifestRed = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/RedBody.css",],
	]);
	$manifestBlue = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/BlueBody.css",],
	]);

	$fingerprintRed = $manifestRed->getFingerprint();
	$fileOrganiserRed = new FileOrganiser($manifestRed);
	$copyDoneRed = $fileOrganiserRed->organise();

	$this->assertTrue($manifestRed->isCacheValid());
	$this->assertTrue($fileOrganiserRed->isStyleFilesCacheValid());
	$this->assertFileExists(APPROOT . "/www/Style_$fingerprintRed");

	$fingerprintBlue = $manifestBlue->getFingerprint();
	$fileOrganiserBlue = new FileOrganiser($manifestBlue);
	$copyDoneBlue = $fileOrganiserBlue->organise();

	$this->assertTrue($manifestBlue->isCacheValid());
	$this->assertTrue($fileOrganiserBlue->isStyleFilesCacheValid());
	$this->assertFileExists(APPROOT . "/www/Style_$fingerprintRed");
	$this->assertFileExists(APPROOT . "/www/Style_$fingerprintBlue");

	// Only change red's content.
	ManifestTest::putApprootFile([
		"/Style/RedBody.css" => 
			"body#red {
				background: red;
				color: white;
			}",
	]);

	$this->assertFalse($fileOrganiserRed->isStyleFilesCacheValid());
	$this->assertFalse($fileOrganiserBlue->isStyleFilesCacheValid());

	$copyDoneRed = $fileOrganiserRed->organise();

	$this->assertTrue($manifestRed->isCacheValid());
	$this->assertFalse($manifestBlue->isCacheValid());
}

/**
 * APPROOT/Asset directory should be copied to the www/Asset directory, an 
 * Asset cache should be created. If the Asset cache is valid, copying should
 * be skipped.
 */
public function testCopyAsset() {
	ManifestTest::putApprootFile([
		"/Asset/ChristmasList.txt" => 
			"Socks,
			Gloves,
			Pants",
	]);

	$manifest = ManifestTest::createManifest();
	$fileOrganiser = new FileOrganiser($manifest);

	$this->assertFileNotExists(APPROOT . "/www/Asset/ChristmasList.txt");
	$fileOrganiser->organise();
	$this->assertFileExists(APPROOT . "/www/Asset/ChristmasList.txt");

	removeTestApp();
	createTestApp();

	ManifestTest::putApprootFile([
		"/Asset/ChristmasList.txt" => 
			"Socks,
			Gloves,
			Pants",
		"/Asset/InnerDirectory/ShoppingList.txt" =>
			"Milk,
			Pie,
			Chocolate",
	]);

	$manifest = ManifestTest::createManifest();
	$fileOrganiser = new FileOrganiser($manifest);

	$fileOrganiser->organise();
	$this->assertFileExists(APPROOT . "/www/Asset/ChristmasList.txt");
	$this->assertFileExists(
		APPROOT . "/www/Asset/InnerDirectory/ShoppingList.txt");
}

}#