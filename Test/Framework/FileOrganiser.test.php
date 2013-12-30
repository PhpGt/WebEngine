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

	require_once(__DIR__ . "/Manifest.test.php");
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
	$this->assertFalse($fileOrganiser->isStyleScriptFilesCacheValid());

	$fileOrganiser->organise();

	$this->assertTrue($manifest->isCacheValid());
	$this->assertTrue($manifest->isCacheValid());
	$this->assertTrue($fileOrganiser->isStyleScriptFilesCacheValid());
	$this->assertTrue($fileOrganiser->isStyleScriptFilesCacheValid());

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
	$this->assertFalse($fileOrganiser->isStyleScriptFilesCacheValid());
	$this->assertFalse($fileOrganiser->isStyleScriptFilesCacheValid());
}

/**
 * Should take all pre-processed files in the www/fingerprint directory, 
 * then combine and minify them into a single file, then remove the originals.
 */
public function testMinify() {
	$approotFileDetails = [
		"/Style/Main.scss" => 
			"body {
				> h1 {
					color: red;
				}
			}",
		"/Style/Another.scss" => 
			"body {
				> h1.blue {
					color: blue;
				}
			}",
		"/Script/One.js" =>
			"alert('one');",
		"/Script/Subdir/Two.js" =>
			"alert('two');",
	];
	ManifestTest::putApprootFile($approotFileDetails);
	$manifest = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/Main.scss", "/Style/Another.scss"],
		"Script" => ["/Script/Gt.js", "/Script/One.js", "/Script/Subdir/Two.js"]
	]);

	$fileOrganiser = new FileOrganiser($manifest);
	$fileOrganiser->organise(true);

	foreach ($approotFileDetails as $file => $contents) {
		foreach (ClientSideCompiler::$sourceMap as $match => $replace) {
			if(preg_match($match, $file)) {
				$file = preg_replace($match, $replace, $file);				
			}
		}
		$this->assertFileNotExists($file);
	}

	// Last check the www minified files are created.
	$fingerprint = $manifest->getFingerprint();
	$scriptMinFilePath = APPROOT . "/www/Min/$fingerprint.js";
	$styleMinFilePath = APPROOT  . "/www/Min/$fingerprint.css";

	$this->assertFileExists($scriptMinFilePath);
	$this->assertFileExists($styleMinFilePath);

	$scriptMinContents = file_get_contents($scriptMinFilePath);
	$styleMinContents = file_get_contents($styleMinFilePath);

	$this->assertContains("alert('one')", $scriptMinContents);
	$this->assertContains("alert('two')", $scriptMinContents);
	$this->assertContains("body > h1", $styleMinContents);
	$this->assertContains("body > h1.blue", $styleMinContents);

	$this->assertNotContains("alert('one')", $styleMinContents);
	$this->assertNotContains("alert('two')", $styleMinContents);

	$this->assertNotContains("body > h1", $scriptMinContents);
	$this->assertNotContains("body > h1.blue", $scriptMinContents);
}

/**
 * If any files change in either APPROOT or GTROOT's Style
 * directory, this should cause the StyleScriptFiles cache to be invalid, and 
 * the StyleScriptFiles cache along with all fingerprint directories should be 
 * removed.
 */
public function testStyleScriptFileModificationRemovesAllCaches() {
	ManifestTest::putApprootFile([
		"/Style/RedBody.css" => 
			"body#red {
				background: red;
			}",
		"/Style/BlueBody.css" =>
			"body#blue {
				background: blue;
			}",
		"/Script/Test.js" =>
			"alert('Just a test...');",
	]);
	$manifestRed = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/RedBody.css",],
	]);
	$manifestBlue = ManifestTest::createManifest([
		"Style" => ["/Style/Gt.css", "/Style/BlueBody.css",],
		"Script" => ["/Script/Test.js"],
	]);

	$fingerprintRed = $manifestRed->getFingerprint();
	$fileOrganiserRed = new FileOrganiser($manifestRed);
	$copyDoneRed = $fileOrganiserRed->organise();

	$this->assertTrue($manifestRed->isCacheValid());
	$this->assertTrue($fileOrganiserRed->isStyleScriptFilesCacheValid());
	$this->assertFileExists(APPROOT . "/www/Style_$fingerprintRed");

	$fingerprintBlue = $manifestBlue->getFingerprint();
	$fileOrganiserBlue = new FileOrganiser($manifestBlue);
	$copyDoneBlue = $fileOrganiserBlue->organise();

	$this->assertTrue($manifestBlue->isCacheValid());
	$this->assertTrue($fileOrganiserBlue->isStyleScriptFilesCacheValid());
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

	$this->assertFalse($fileOrganiserRed->isStyleScriptFilesCacheValid());
	$this->assertFalse($fileOrganiserBlue->isStyleScriptFilesCacheValid());

	$copyDoneRed = $fileOrganiserRed->organise();

	$this->assertTrue($manifestRed->isCacheValid());
	$this->assertFalse($manifestBlue->isCacheValid());

	// Change blue's scropt content.
	ManifestTest::putApprootFile([
		"/Script/Test.js" => 
			"alert('Modified');",
	]);

	$this->assertFalse($fileOrganiserRed->isStyleScriptFilesCacheValid());
	$this->assertFalse($fileOrganiserBlue->isStyleScriptFilesCacheValid());

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

	$this->assertFalse($fileOrganiser->isAssetFilesCacheValid());

	$this->assertFileNotExists(APPROOT . "/www/Asset/ChristmasList.txt");
	$fileOrganiser->organise();
	$this->assertFileExists(APPROOT . "/www/Asset/ChristmasList.txt");
	$this->assertTrue($fileOrganiser->isAssetFilesCacheValid());

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
	$this->assertTrue($fileOrganiser->isAssetFilesCacheValid());

	ManifestTest::putApprootFile([
		"/Asset/InnerDirectory/ShoppingList.txt" =>
			"Milk,
			Pie,
			Chocolate,
			Pancakes!",
	]);
	$this->assertFalse($fileOrganiser->isAssetFilesCacheValid());

	unlink(APPROOT . "/Asset/ChristmasList.txt");
	$this->assertFalse($fileOrganiser->isAssetFilesCacheValid());
}

}#