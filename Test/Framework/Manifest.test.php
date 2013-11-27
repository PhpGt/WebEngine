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

	// TODO!
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
			. "\n" // blank line for good measure.
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

}#