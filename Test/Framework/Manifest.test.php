<?php class ManifestTest extends PHPUnit_Framework_TestCase {
private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Manifest Test</title>
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
	require_once(GTROOT . "/Framework/FileOrganiser.php");
	require_once(GTROOT . "/Framework/ClientSideCompiler.php");
}

public function tearDown() {
	removeTestApp();
}

private function createManifestFiles($name, $manifestContents, $fileContents) {
	$sourceFileList = [];
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
			$sourceFileList[] = "$type/$fileName";
		}
	}

	return $sourceFileList;
}

private function getDomHead($manifests = ["TestManifest"]) {
	$dom = new Dom($this->_html);
	$domHead = $dom["html > head"][0];

	foreach ($manifests as $m) {
		$metaEl = $dom->createElement("meta", [
			"name" => "manifest",
			"content" => $m,
		]);
		$domHead->appendChild($metaEl);
	}

	return $domHead;
}

public function testManifestGetsList() {
	$domHead = $this->getDomHead();

	$manifestList = Manifest::getList($domHead);
	$this->assertEquals(1, count($manifestList));

	// Add another manifest file.
	$dom = $domHead->_dom;
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

	$domHead = $this->getDomHead();

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
	// Test with just one manifest first:
	$sourceFiles = $this->createManifestFiles("TestManifest", [
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

	$domHead = $this->getDomHead();
	$metaList = $domHead->xPath(".//meta[@name='manifest']");
	$this->assertEquals(1, $metaList->length);
	$scriptStyleList = $domHead["script, link"];
	$this->assertEquals(0, $scriptStyleList->length);
	
	$manifestList = Manifest::getList($domHead);
	// Even though FileOrganiser is used, actual injection of head elements is
	// done in the Manifest still (file organiser is used to get processed 
	// names of files as their extensions may have to change).
	$fileOrganiser = new FileOrganiser($manifestList);
	$fileOrganiser->organiseManifest($domHead);

	$metaList = $domHead->xPath(".//meta[@name='manifest']");
	$this->assertEquals(0, $metaList->length);

	$scriptStyleList = $domHead["script, link"];
	$this->assertEquals(5, $scriptStyleList->length);

	foreach ($scriptStyleList as $el) {
		$source = "";
		if($el->hasAttribute("href")) {
			$source = $el->getAttribute("href");
		}
		else {
			$source = $el->getAttribute("src");
		}

		// Obtain source without extension (for matching against originals).
		$sourceSubstr = substr($source, 0, stripos($source, "."));
		$sourceSubstr = substr($sourceSubstr, strpos($sourceSubstr, "/") + 1);
		$sourceSubstr = substr($sourceSubstr, strpos($sourceSubstr, "/") + 1);
		$match = false;

		foreach ($sourceFiles as $sourceFile) {
			$sourceFileSubstr = substr(
				$sourceFile, 0, stripos($sourceFile, "."));
			$sourceFileSubstr = substr(
				$sourceFileSubstr, strpos($sourceFileSubstr, "/") + 1);

			if($sourceSubstr == $sourceFileSubstr) {
				$match = true;
			}
		}

		$this->assertTrue($match, "Head source found in original source");
	}

	// Test with more than one manifest:
}

}#