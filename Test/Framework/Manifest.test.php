<?php class ManifestTest extends PHPUnit_Framework_TestCase {
private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Manifest Test</title>
	<meta name="manifest" content="TestManifest" />
</head>
<body>
	<h1>Manifest Test</h1>
</body>
</html>
HTML;

public function setup() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	require_once(GTROOT . "/Framework/Component/Dom.php");
	require_once(GTROOT . "/Framework/Component/DomEl.php");
	require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	require_once(GTROOT . "/Framework/Manifest.php");
}

public function tearDown() {
	removeTestApp();
}

public function testManifestGetsList() {
	$html = $this->_html;
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$manifestList = Manifest::getList($domHead);
	$this->assertEquals(1, count($manifestList));

	// Add another manifest file.
	$metaEl = $dom->createElement("meta", [
		"name" => "manifest",
		"content" => "AnotherTestManifest",
	]);
	$domHead->appendChild($metaEl);

	$manifestList = Manifest::getList($domHead);
	$this->assertEquals(2, count($manifestList));
}

/**
 * Ensures that the files listed inside .manifest files are read correctly,
 * as well as checking for misreads on blank lines and comments.
 */
public function testManifestFilesRead() {
	$styleManifestPath = APPROOT . "/Style/TestManifest.manifest";
	$styleManifest = "Main.css";
	$styleContents = ["Main.css" => "* { color: red; }"];

	$scriptManifestPath = APPROOT . "/Script/TestManifest.manifest";
	$scriptManifest = "Main.js
# This is a comment, followed by a new line.

Go/*";
	$scriptContents = [
		"Main.js" => "Just a test",
		"Go/Test1.js" => "Some content here",
		"Go/Test2.js" => "it doesn't need to be valid javascript",
		"Go/Test3.js" => "as we are just testing if the file list is built.",
	];

	if(!is_dir(dirname($styleManifestPath))) {
		mkdir(dirname($styleManifestPath, 0775, true));
	}
	file_put_contents($styleManifestPath, $styleManifest);
	foreach ($styleContents as $fileName => $contents) {
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
		if(!is_dir(dirname($fileName))) {
			mkdir(dirname($fileName), 0775, true);
		}
		file_put_contents($fileName, $contents);
	}

	$html = $this->_html;
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$manifestList = Manifest::getList($domHead);
	$fileList = $manifestList[0]->getFiles();

	var_dump($fileList);die();

	$this->assertCount(0, $fileList["Style"]);
	$this->assertCount(4, $fileList["Script"]);
}

}#