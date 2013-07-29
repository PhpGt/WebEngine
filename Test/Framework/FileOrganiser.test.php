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

}

}#