<?php class ManifestTest extends PHPUnit_Framework_TestCase {

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

/**
 * Creates an HTML string to use with the DOM's constructor. Takes an 
 * associative array of elements to output in the head (Script, Style), and an
 * array of meta tags to produce.
 * Meta tags are always output just beneath the <title> element.
 */
public static function createHtmlString(
$elementArray = array(), $metaArray = array()) {

	$html = "<!doctype html>
		<html>
		<head>
			<meta charset='utf-8' />
			<title>Manifest Test Document</title>";
	
	foreach ($metaArray as $meta) {
		$html .= "\n<meta name='manifest' content='$meta' />";
	}

	$html .= "\n";

	foreach ($elementArray as $type => $sourceArray) {
		$typeDetails = Manifest::$elementDetails[$type];
		foreach ($sourceArray as $source) {
			$html .= "\n<" . $typeDetails["TagName"];
			$html .= " " . $typeDetails["Source"]
				. "='$source'";

			foreach ($typeDetails["ReqAttr"] as $key => $value) {
				$html .= " $key='$value'";
			}

			if($typeDetails["EndTag"]) {
				$html .= "></" . $typeDetails["TagName"] . ">";
			}
			else {
				$html .= " />";
			}
		}
	}

	$html .= "\n</head>
			<body><h1>Manifest Test!!!</h1></body>
		</html>";

	return $html;
}

/**
 * Used as shorthand to create a Manifest object, passing all arguments to 
 * the createHtmlString method.
 */
public static function createManifest() {
	$html = call_user_func_array("ManifestTest::createHtmlString", func_get_args());
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];
	$manifest = new Manifest($domHead, true);
	return $manifest;
}

public static function putApprootFile($fileArray /*, [$fileArrayN ..] */ ) {
	$fileArrayArray = func_get_args();
	foreach ($fileArrayArray as $fileArray) {
		if(array_keys($fileArray) !== range(0, count($fileArray) - 1)) {
			// Array is associative.
			foreach ($fileArray as $file => $content) {
				$filePath = APPROOT . $file;
				if(!is_dir(dirname($filePath))) {
					mkdir(dirname($filePath), 0775, true);
				}
				file_put_contents($filePath, $content);
			}
		}
		else {
			// Array is not associative.
			foreach ($fileArray as $file) {
				$filePath = APPROOT . $file;
				if(!is_dir(dirname($filePath))) {
					mkdir(dirname($filePath), 0775, true);
				}
				touch($filePath);
			}
		}
	}		
}
/**
 * A manifest represents the current request's DOM head. The head can have 
 * <meta name="manifest"> tags to represent a collection of files. The meta
 * tags are optional, but if exist, the files should be injected in-place, 
 * and the meta tags should be removed before rendering.
 */
public function testExpandsMetaTags() {
	// Run the test with no manifest files created.
	$html = $this->createHtmlString([], ["ManifestOne", "ManifestTwo"]);
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$this->assertInstanceOf("DomEl", $domHead);

	$metaTagList = $domHead["meta[name='manifest']"];
	$this->assertEquals(2, $metaTagList->length);

	// Constructing the manifest consumes any meta tags.
	$manifest = new Manifest($domHead, true);
	$metaTagList = $domHead["meta[name='manifest']"];
	$this->assertEquals(0, $metaTagList->length);

	// Make sure the other meta tag is untouched.
	$metaTagList = $domHead["meta"];
	$this->assertEquals(1, $metaTagList->length);

	// Run the same test again, this time with some manifest files created.
	removeTestApp();
	createTestApp();
	$this->putApprootFile([
		"/Script/ManifestOne.manifest" => 
			"FileOne.js
			FileTwo.js

			#Comment Line
			FileThree.js",
		"/Style/ManifestTwo.manifest" =>
			"FileTen.css
			FileEleven.css",
	]);
	
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$manifest = new Manifest($domHead, true);
	$scriptLinkList = $domHead["script, link"];
	$this->assertEquals(5, $scriptLinkList->length);

	// Run the same test again, this time with all manifest files created.
	removeTestApp();
	createTestApp();
	$this->putApprootFile([
		"/Script/ManifestOne.manifest" => 
			"FileOne.js
			FileTwo.js

			#Comment Line
			FileThree.js",
		"/Script/ManifestTwo.manifest" => 
			"FileFour.js
			FileFive.js",
		"/Style/ManifestOne.manifest" => 
			"FileSix.css
			FileSeven.css
			FileEight.css
			FileNine.css",
		"/Style/ManifestTwo.manifest" =>
			"FileTen.css
			FileEleven.css",
	]);

	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];

	$manifest = new Manifest($domHead, true);
	$scriptLinkList = $domHead["script, link"];
	$this->assertEquals(11, $scriptLinkList->length);
}

/**
 * To know what dom head is being represented, a fingerprint is made on the
 * contents of the head's external link/script tags. If the order of external
 * scripts changes, so should the fingerprint.
 */
public function testFingerprintDomHead() {
	// First, run the tests using manifest meta tags, with no manifest files.
	// This will create a manifest that represents an empty dom head.
	$manifest = self::createManifest([], ["ManifestOne", "ManifestTwo"]);
	$fingerprintNoManifestFiles = $manifest->getFingerprint();

	// Second, run the tests again, using an empty dom head.
	// The two fingerprints should match.
	$manifest = self::createManifest();
	$fingerprintEmptyHead = $manifest->getFingerprint();

	$this->assertEquals($fingerprintNoManifestFiles, $fingerprintEmptyHead);

	// Now ensure that the fingerprints do not match when an element exists.
	$manifest = self::createManifest(["Style" => ["/Style/Main.scss"]]);
	$fingerprintSingleElement = $manifest->getFingerprint();

	$this->assertNotEquals($fingerprintSingleElement, $fingerprintEmptyHead);

	// Finally, ensure that a fingerprint made using a meta tag matches a
	// fingerprint made from an element, if the filenames are the same.
	$this->putApprootFile([
		"/Style/FPrintTest.manifest" => "#This is a manifest file!
			/Style/Main.scss",
	]);
	$manifest = self::createManifest([], ["FPrintTest"]);
	$fingerPrintSingleManifest = $manifest->getFingerprint();

	$this->assertEquals($fingerPrintSingleManifest, $fingerprintSingleElement);
}

/**
 * Once meta tags are expanded, the manifest should list all source files in an
 * associative array, in the correct order.
 * Using an asterisk as the last character in a manifest file's line should
 * load all files within the directory, recursively.
 */
public function testPathArray() {
	$manifestFileArray = [
		"/Script/TestOne.manifest" => 
			"/Script/FileOne.js
			 /Script/FileTwo.js",
		"/Style/TestOne.manifest" =>
			"/Style/FileThree.css
			 /Style/FileFour.css",

		"/Style/TestTwo.manifest" =>
			"/Style/FileFive.css
			 /Style/FileSix.css",
	];

	$this->putApprootFile($manifestFileArray);
	$manifest = self::createManifest([], ["TestOne", "TestTwo"]);
	$pathArray = $manifest->getPathArray();

	// Build up an array containing just the file paths of manifestFileArray.
	$originalPathArray = array();
	foreach ($manifestFileArray as $file => $contents) {
		$lines = explode("\n", $contents);
		$lines = array_map("trim", $lines);
		$originalPathArray = array_merge($originalPathArray, $lines);
	}

	$this->assertEquals($originalPathArray, $pathArray);

	// Test with a recursive directory.
	$manifestFileArray = [
		"/Script/TestThree.manifest" => 
			"/Script/Go/*",
	];
	// The actual files inside Go need creating, so the Manifest can see them.
	$originalPathArray = [
		"/Script/Go/One.js",
		"/Script/Go/Two.js",
		"/Script/Go/Three.js",
		"/Script/Go/InnerDir/Four.js",
	];
	$this->putApprootFile($manifestFileArray, $originalPathArray);
	$manifest = self::createManifest([], ["TestThree"]);
	$pathArray = $manifest->getPathArray();

	foreach ($pathArray as $path) {
		$this->assertContains($path, $originalPathArray);
	}
}

/**
 * If a directory exists for the current manifest within the www directory, the
 * cache is assumed to be valid. Cache directories are simply named according to
 * the manifest's fingerprint.
 */
public function testCacheValidity() {
	$this->putApprootFile([
		"/Style/FirstCacheTest.manifest" => "/Style/Main.scss",
	]);
	$manifest = self::createManifest([], ["FirstCacheTest"]);
	$fingerprint = $manifest->getFingerprint();
	
	$this->assertFalse($manifest->isCacheValid());
	mkdir(APPROOT . "/www/Style_$fingerprint", 0775, true);
	$this->assertTrue($manifest->isCacheValid());
}

/**
 * Because the source DOM head will point to files in their source directories
 * such as /Style/Main.css, and the files are copied to a fingerprint directory,
 * the DOM head needs expanding to point to the public files within their
 * fingerprint directory.
 */
public function testExpandDomHead() {
	$html = $this->createHtmlString([
		"Script" => ["/Script/Main.js"],
		"Style" => ["/Style/Main.scss"],
	]);
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];
	$manifest = new Manifest($domHead);
	$fingerprint = $manifest->getFingerprint();

	$elementArray = $domHead["script, link"];
	foreach ($elementArray as $element) {
		foreach (Manifest::$elementDetails as $type => $typeDetails) {
			if(strtolower($typeDetails["TagName"])
			== strtolower($element->tagName)) {
				$source = $element->getAttribute($typeDetails["Source"]);

				$this->assertStringStartsWith("/{$type}_$fingerprint", $source);
			}
		}
	}

	// Test again, this time with a combination of manifest files and head
	// elements.
	removeTestApp();
	createTestApp();

	$this->putApprootFile([
		"/Script/ManifestOne.manifest" => "/Script/Gt.js",
		"/Script/ManifestTwo.manifest" => "/Script/LastScript.js",
		"/Style/ManifestTwo.manifest" => "/Style/Gt.css",
	]);

	$html = $this->createHtmlString([
		"Script" => ["/Script/Main.js"],
		"Style" => ["/Style/Main.scss"],
	], ["ManifestOne", "ManifestTwo"]);
	$dom = new Dom($html);
	$domHead = $dom["html > head"][0];
	$manifest = new Manifest($domHead);
	$fingerprint = $manifest->getFingerprint();

	$elementArray = $domHead["script, link"];
	foreach ($elementArray as $element) {
		foreach (Manifest::$elementDetails as $type => $typeDetails) {
			if(strtolower($typeDetails["TagName"])
			== strtolower($element->tagName)) {
				$source = $element->getAttribute($typeDetails["Source"]);

				$this->assertStringStartsWith("/{$type}_$fingerprint", $source);
			}
		}
	}
}

}#