<?php class ManifestTest extends PHPUnit_Framework_TestCase {
private $_html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Manifest Test</title>
	<meta name="manifest" content="_Default" />
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

public function testManifestRemovesMetaManifestTag() {
	$html = $this->_html;
	$dom = new Dom($html);
	
	$domHead = $dom["html > head"][0];

	$manifest = new Manifest($domHead);

	$metaList = $dom["head > meta"];
	$metaManifestArray = array();
	foreach ($metaList as $meta) {
		if($meta->hasAttribute("name")) {
			if($meta->getAttribute("name") == "manifest") {
				$metaManifestArray[] = $meta;
			}
		}
	}
	$this->assertEquals(0, count($metaManifestArray),
		"Number of meta manifest elements in head.");
}

/**
 * Tests that the manifest for given files renders a DOM head with the
 * appropriate elements, in the correct order.
 */
public function testManifestCreatesDomHead() {
	// First create the manifest files:
	$mfFiles = array(
		APPROOT . "/Script/_Default.manifest" => 
			"/Main.js\n"
			. "/Another.js\n",
		APPROOT . "/Style/_Default.manifest" =>
			"\n" // Ensure that new lines don't break things.
			. "/Main.css\n"
			. "/_Common.css\n"
			. "/Index.css\n",
	);
	foreach ($mfFiles as $path => $content) {
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, $content);
	}

	$html = $this->_html;

	$dom = new Dom($html);
	$manifest = new Manifest($dom["head"]);

	$scriptList = $dom["head > script"];
	$styleList = $dom["head > link"];

	$this->assertEquals(2, $scriptList->length, "Number of scripts in head.");
	$this->assertEquals(3, $styleList->length, "Number of styles in head.");
}

public function testManifestLinksMatch() {
	// First create the manifest files:
	$mfFiles = array(
		APPROOT . "/Script/_Default.manifest" => 
			"/Main.js\n"
			. "/Another.js\n",
		APPROOT . "/Style/_Default.manifest" =>
			"/Main.css\n"
			. "/_Common.css\n"
			. "/Index.css\n",
	);
	foreach ($mfFiles as $path => $content) {
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, $content);
	}

	$html = $this->_html;

	$dom = new Dom($html);
	$manifest = new Manifest($dom["head"]);

	$scriptList = $dom["head > script"];
	$styleList = $dom["head > link"];

	$expectedLines = explode("\n", 
		$mfFiles[APPROOT . "/Script/_Default.manifest"]);
	foreach ($scriptList as $i => $script) {
		$expected = $expectedLines[$i];
		$this->assertEquals(
			$expected,
			$script->src);
	}

	$expectedLines = explode("\n", 
		$mfFiles[APPROOT . "/Style/_Default.manifest"]);
	foreach ($styleList as $i => $style) {
		$expected = $expectedLines[$i];
		$this->assertEquals(
			$expected,
			$style->href);
	}
}

/**
 * Tests that comments within manifest files are ignored.
 */
public function testManifestComments() {
	// First create the manifest files:
	$mfFiles = array(
		APPROOT . "/Script/_Default.manifest" => 
			"# This is a comment\n"
			. "/Main.js\n"
			. "   #   This is a comment with spaces\n"
			. "/Another.js\n",
	);
	foreach ($mfFiles as $path => $content) {
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, $content);
	}

	$html = $this->_html;

	$dom = new Dom($html);
	$manifest = new Manifest($dom["head"]);

	$scriptList = $dom["head > script"];
	$this->assertEquals(2, $scriptList->length, "Number of scripts in head.");
}

/**
 * Tests that the manifest when using a different named manifest.
 */
public function testManifestNonDefault() {
	// First create the manifest files:
	$mfFiles = array(
		APPROOT . "/Script/TestName.manifest" => 
			"/One.js\n"
			. "/Two.js\n"
			. "/Three.js\n"
			. "/Four.js\n",
		APPROOT . "/Style/TestName.manifest" =>
			"/One.css\n"
			. "/Two.css\n"
			. "/Three.css\n",
	);
	foreach ($mfFiles as $path => $content) {
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, $content);
	}

	$html = str_replace("_Default", "TestName", $this->_html);

	$dom = new Dom($html);
	$manifest = new Manifest($dom["head"]);

	$scriptList = $dom["head > script"];
	$styleList = $dom["head > link"];
	$this->assertEquals(4, $scriptList->length, "Number of scripts in head.");
	$this->assertEquals(3, $styleList->length, "Number of styles in head.");
}

/**
 * Tests that the order of elements in the head matches the order specified in
 * the manifest files.
 */
public function testManifestFileOrderPreserved() {
	$mfFiles = array(
		APPROOT . "/Script/_Default.manifest" => 
			"/1.js\n"
			. "/2.js\n"
			. "/3.js\n",
		APPROOT . "/Style/_Default.manifest" =>
			"/1.css\n"
			. "/2.css\n"
			. "/3.css\n",
	);
	foreach ($mfFiles as $path => $content) {
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, $content);
	}

	$html = $this->_html;

	$dom = new Dom($html);
	$manifest = new Manifest($dom["head"]);

	$scriptList = $dom["head > script"];
	$lastNum = -1;
	
	foreach ($scriptList as $script) {
		$src = $script->getAttribute("src");
		$matches = [];
		preg_match("/\d/", $src, $matches);
		$num = $matches[0];

		$this->assertGreaterThan($lastNum, $num);
		$lastNum = $num;
	}

	$styleList = $dom["head > link"];
	$lastNum = -1;
	
	foreach ($styleList as $style) {
		$href = $style->getAttribute("href");
		$matches = [];
		preg_match("/\d/", $href, $matches);
		$num = $matches[0];

		$this->assertGreaterThan($lastNum, $num);
		$lastNum = $num;
	}
}

}#