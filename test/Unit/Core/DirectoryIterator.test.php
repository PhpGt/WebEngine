<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class DirectoryIteratorTest extends \PHPUnit_Framework_TestCase {

public function setUp() {
	$tmp = \Gt\Test\Helper::createTmpDir();
}

public function tearDown() {
	\Gt\Test\Helper::cleanup(Path::get(Path::ROOT));
}

public function testCallback() {
	$path = Path::get(Path::ROOT);
	$filePathArray = [];

	for ($depth = 0; $depth < 5; $depth++) {
		$path .= "/" . uniqid();
		mkdir($path);

		for ($directoryIndex = 0; $directoryIndex < 5; $directoryIndex++) {
			$filePath = $path . "/" . uniqid();
			$filePathArray []= $filePath;
			file_put_contents($filePath, "This is file $filePath");
		}
	}

	$outputArray = DirectoryIterator::walk(
		Path::get(Path::ROOT), [$this, "walkCallback"]);
	$this->assertInternalType("array", $outputArray);

	foreach ($outputArray as $outputItem) {
		$innerFilePath = substr($outputItem, strpos($outputItem, "/"));
		$this->assertFileExists($innerFilePath);
	}
}

/**
 * @expectedException \Gt\Core\Exception\RequiredAppResourceNotFoundException
 */
public function testWalkOnDirectoryThatDoesNotExist() {
	DirectoryIterator::walk("/" . uniqid(true), null);
}

public function testHash() {
	/**
	 	TODO:
	 	files are being created wrong... loop logic wrong.
	 			me tired
	 						me sleep now
	 */
	$path = Path::get(Path::ROOT);
	$md5Array = [];

	for ($depth = 0; $depth < 5; $depth++) {
		$path = "/" . uniqid();
		mkdir($path);

		for ($directoryIndex = 0; $directoryIndex < 5; $directoryIndex++) {
			$filePath = $path . "/" . uniqid();
			file_put_contents($filePath . ".file",
				"This is file $filePath.file");
			$md5Array []= "$filePath\n";
			// $md5Array []= md5($filePath) . md5_file($filePath);
		}
	}

	sort($md5Array);
	$md5 = implode("", $md5Array);
	// $md5 = md5($md5);

	$hash = DirectoryIterator::hash(Path::get(Path::ROOT));
	$this->assertEquals($md5, $hash);
}

/**
 *
 */
public function walkCallback($file, $iterator) {
	$path = $file->getPathname();
	return $path;
}

}#