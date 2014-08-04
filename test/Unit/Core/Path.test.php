<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Path_Test extends \PHPUnit_Framework_TestCase {

private $tmp;
private $root;
private $src;

public function setup() {
	$this->tmp = sys_get_temp_dir() . "/gt-temp-path-test";
	if(!is_dir($this->tmp)) {
		mkdir($this->tmp, 0775, true);
	}

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
		$constValue = constant("Gt\Core\Path::$constName");
		$this->assertNotEmpty($constValue);
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

	$pageViewPath = $this->src . "/page/View";
	$pageViewFilePath = "/Subdirectory/CAPITALS/FILE_test";
	$pageViewExtension = ".html";
	$pageViewPath_full = $pageViewPath . $pageViewFilePath . $pageViewExtension;
	$pageViewPath_uri = $pageViewFilePath;

	mkdir(dirname($pageViewPath_full), 0775, true);
	file_put_contents($pageViewPath_full, "<!doctype html><h1>TEST!</h1>");

	$this->assertEquals($pageViewPath_full,
		Path::fixCase(Path::get(Path::PAGEVIEW)
		. "/subdirectory/capitals/file_test.html")
	);

	// Test URI-style
	$uri = "/subDirectory/Capitals/File_Test";
	$uriWithExtension = $uri . $pageViewExtension;
	$this->assertEquals($pageViewPath_uri . $pageViewExtension,
		Path::fixCase($uriWithExtension, true));

	// Test URI-style with no extension
	$this->assertEquals($pageViewPath_uri . $pageViewExtension,
		Path::fixCase($uri, true));
}

}#