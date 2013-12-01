<?php class ClientSideCompilerTest extends PHPUnit_Framework_TestCase {

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

/**
 * Loads an .scss file and makes sure that it processes to proper CSS.
 */
public function testSassProcesses() {
	$scss = "\$myColour = rgba(red, 0.8);
		body { h1 { background: \$myColour; } }";
	$scssFile = APPROOT . "/Style/Main.scss";
	if(!is_dir(dirname($scssFile))) {
		mkdir(dirname($scssFile), 0775, true);
	}
	file_put_contents($scssFile, $scss);

	$processed = ClientSideCompiler::process($scssFile, $scssFile . ".css");
	$this->assertContains("body h1", $processed["Contents"]);
}

}#