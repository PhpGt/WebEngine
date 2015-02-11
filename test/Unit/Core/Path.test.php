<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class Path_Test extends \PHPUnit_Framework_TestCase {

private $tmp;
private $root;
private $src;

public function setup() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();
	$this->root = $this->tmp . "/www";
	$this->src = $this->tmp . "/src";
	$_SERVER["DOCUMENT_ROOT"] = $this->tmp . "/www";
}

public function teardown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

public function testPathThrowsExceptionFromInvalidConstant() {
	$this->setExpectedException("\UnexpectedValueException");
	// Invalid constant...
	Path::get("spam");
}

public function testRootSet() {
	$this->assertEquals(dirname($this->root), Path::get(Path::ROOT));
}

public function testGetSubPaths() {
	$refl = new \ReflectionClass("Gt\Core\Path");
	$constantsArray = $refl->getConstants();

	foreach ($constantsArray as $constName) {
		$constName = strtoupper($constName);
		$constValue = constant("Gt\Core\Path::$constName");
		$this->assertNotEmpty($constValue);

		$cfg = new \Gt\Core\ConfigObj(["api_directory" => "api"]);
		$cfg->setName("api");
		Path::setConfig($cfg);
		$path = Path::get($constValue);

		$this->assertNotEmpty($path);
		if($constName !== "GTROOT") {
			$this->assertContains(dirname($this->root), $path);
		}
	}
}

public function testGetGtRoot() {
	$gtroot = realpath(__DIR__ . "/../../../");
	$this->assertEquals($gtroot, Path::get(Path::GTROOT));
}

public function testFixCase() {
	$styleSubDir = $this->src . "/style/DirName";
	mkdir($styleSubDir, 0775, true);

	$this->assertEquals($styleSubDir,
		Path::fixCase(Path::get(Path::STYLE) . "/DirName"));

	$pageViewPath = $this->src . "/page";
	$pageViewFilePath = "/Subdirectory/CAPITALS/FILE_test";
	$pageViewExtension = ".html";
	$pageViewPath_full = $pageViewPath . $pageViewFilePath . $pageViewExtension;
	$pageViewPath_uri = $pageViewFilePath;

	mkdir(dirname($pageViewPath_full), 0775, true);
	file_put_contents($pageViewPath_full, "<!doctype html><h1>TEST!</h1>");

	$this->assertEquals($pageViewPath_full,
		Path::fixCase(Path::get(Path::PAGE)
		. "/subdirectory/capitals/file_test.html")
	);

	// Test URI-style
	$pvPath = Path::get(Path::PAGE);
	$uri = "/subDirectory/Capitals/File_Test";
	$uriWithExtension = $uri . $pageViewExtension;

	$this->assertEquals(Path::fixCase($pageViewPath_uri),
		Path::fixCase($pvPath . $uri, $pvPath, $pageViewExtension));

	// Test URI-style with no extension
	$this->assertEquals(Path::fixCase($pageViewPath_uri . $pageViewExtension),
		Path::fixCase($pvPath . $uriWithExtension, $pvPath));

}

}#