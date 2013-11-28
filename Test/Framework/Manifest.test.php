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

private function createManifestFiles($name, $manifestContents, $fileContents) {
	// Create the contents of the .manifest file:
	$manifestFile = "$name.manifest";
	foreach ($manifestContents as $type => $contents) {
		$manifestFilePath = APPROOT . "/$type/$manifestFile";
		if(!is_dir(dirname($manifestFilePath))) {
			mkdir(dirname($manifestFilePath), 0775, true);
		}
		file_put_contents($manifestFilePath, $contents);
	}

	// Create the contents of the refrenced source files:
	foreach ($fileContents as $type => $fileList) {
		$typePath = APPROOT . "/$type";
		foreach ($fileList as $fileName => $contents) {
			$filePath = "$typePath/$fileName";
			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}

			file_put_contents($filePath, $contents);
		}
	}
}

/**
 * Ensures that the files listed inside .manifest files are read correctly,
 * as well as checking for misreads on blank lines and comments.
 */
public function testManifestFilesRead() {
	$this->createManifestFiles("TestManifest", [
		"Style" => "Main.css",
		"Script" => "Main.js
			#This is a comment, followed by a new line.

			Go/*",
	], [
		"Style" => [
			"Main.css" => "* { color: red; }",
		],
		"Script" => [
			"Main.js" => "Just a test",
			"Go/Test1.js" => "Some content here",
			"Go/Test2.js" => "it doesn't need to be valid javascript",
			"Go/Test3.js" => "as we're just testing if the file list is built.",
		],
	]);

	$html = $this->_html;
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$manifestList = Manifest::getList($domHead);
	$fileList = $manifestList[0]->getFiles();

	$this->assertCount(1, $fileList["Style"], "List of Style files");
	$this->assertCount(4, $fileList["Script"], "List of Script files");
}

/**
 * Ensures that the md5 returned by the getMd5 function matches the md5 of
 * actual files mentioned in a .manifest file.
 */
public function testManifestMd5() {
	$md5 = "";

	$sourceContents = [
		"Script" => [
			"Main.js" => "Just a test",
			"Go/Test1.js" => "Some content here",
			"Go/Test2.js" => "it doesn't need to be valid javascript",
			"Go/Test3.js" => "as we're just testing if the file list is built.",
		],
	];

	$this->createManifestFiles("TestManifest", [
		"Script" => "Main.js
			Go/*"
	], $sourceContents);

	foreach ($sourceContents["Script"] as $fileName => $contents) {
		// Store the md5 of actual contents:
		$md5 .= md5($contents);
	}

	$md5 = md5($md5);

	$manifest = new Manifest("TestManifest");
	$testMd5 = $manifest->getMd5();

	$this->assertEquals($testMd5, $md5);
}

/**
 * Ensures that after the processing has taken place that the end result in the
 * DOM head actually has the meta tag replaced with the correct elements.
 */
public function testManifestHeadTagsReplaced() {
	$this->createManifestFiles("TestManifest", [
		"Style" => "Main.css",
		"Script" => "Main.js
			#This is a comment, followed by a new line.

			Go/*",
	], [
		"Style" => [
			"Main.css" => "* { color: red; }",
		],
		"Script" => [
			"Main.js" => "Just a test",
			"Go/Test1.js" => "Some content here",
			"Go/Test2.js" => "it doesn't need to be valid javascript",
			"Go/Test3.js" => "as we're just testing if the file list is built.",
		],
	]);
}

}#