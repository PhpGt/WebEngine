<?php class FileOrganiserTest extends PHPUnit_Framework_TestCase {

private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" href="/Main.scss" />
	<script src="/Gt.js"></script>
	<script src="/AppScript.js"></script>
</head>
<body>
	<h1>Hello, PHPUnit!</h1>
</body>
</html>
HTML;


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
 * Test that only core PHP.Gt client-side files are set to copy over on an
 * empty project.
 */
public function testCheckFilesWhenEmpty() {
	// Test that only core PHP.Gt
	$webroot = APPROOT . "/www";
	$fo = new FileOrganiser();
	$files = $fo->checkFiles();

	// TODO: Create array from actual PHP.Gt files here.

	$this->assertEmpty($files);
}

public function testCheckFilesWhenNotEmpty() {
	// Test that there is something to copy over when there are files in the
	// Asset, Script or Style directories.
	$webroot = APPROOT . "/www";
	file_put_contents(APPROOT . "/Asset/SomeAssetData.dat", "Asset contents");
	file_put_contents(APPROOT . "/Script/Main.js", "alert('Script!')");
	file_put_contents(APPROOT . "/Style/Main.css", "* { color: red; }");

	$fo = new FileOrganiser();
	$files = $fo->checkFiles();

	// TODO: Create array from file_put_contents scripts above.
}

}#