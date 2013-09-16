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
	$html = $this->_html;
	$mfFiles = array(
		APPROOT . "/Script/_Default.manifest",
		APPROOT . "/Style/_Default.manifest",
	);

	$dom = new Dom($html);
	$scriptList = $dom["head > script"];
	$this->assertEquals(2, $scriptList->length, "Number of scripts in head.");
}

}#