<?php class ManifestTest extends PHPUnit_Framework_TestCase {

public function setup() {
	removeTestApp();
	createTestApp();
	// require_once(GTROOT . "/Class/Css2Xpath/Css2Xpath.class.php");
	// require_once(GTROOT . "/Framework/Component/Dom.php");
	// require_once(GTROOT . "/Framework/Component/DomEl.php");
	// require_once(GTROOT . "/Framework/Component/DomElClassList.php");
	// require_once(GTROOT . "/Framework/Component/DomElCollection.php");
	// require_once(GTROOT . "/Framework/FileOrganiser.php");
	// require_once(GTROOT . "/Framework/ClientSideCompiler.php");
}

public function tearDown() {
	removeTestApp();
}

/**
 * Tests that when only using a single _Header.html file, the manifest object
 * is not used at all.
 */
public function testManifestIsNotRequiredOnSingleDomHead() {
	// TODO: 103:
}

/**
 * Tests that when multiple _Header.html files are used *without* a manifest
 * that a meaningful 500 error is thrown to tell the developer that they should
 * be using a manifest file.
 */
public function testMultipleHeaderHtmlFilesTriggerManifestError() {
	// TODO: 103:
}

/**
 * Tests that the manifest for given files renders a DOM head with the
 * appropriate elements, in the correct order.
 */
public function testManifestCreatesDomHead() {
	// TODO: 103:
}

}#