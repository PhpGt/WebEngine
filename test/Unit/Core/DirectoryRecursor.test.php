<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class DirectoryRecursorTest extends \PHPUnit_Framework_TestCase {

public function setUp() {
	$tmp = \Gt\Test\Helper::createTmpDir();
}

public function tearDown() {
	\Gt\Test\Helper::cleanup(Path::get(Path::ROOT));
}

public function testCallback() {
	$path = Path::get(Path::ROOT);
	$filePathArray = [];

	$this->createDirectoryStructure($path, 5, 5);

	$outputArray = DirectoryRecursor::walk(
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
	DirectoryRecursor::walk("/" . uniqid(true), null);
}

public function testHash() {
	$path = Path::get(Path::ROOT);
	$this->createDirectoryStructure($path, 5, 5);

	$md5Array = DirectoryRecursor::walk($path, [$this, "hashCallback"]);
	$md5 = implode("", $md5Array);

	$md5 = md5($md5);

	$hash = DirectoryRecursor::hash(Path::get(Path::ROOT));
	$this->assertEquals($md5, $hash);
}

public function testPurge() {
	$path = Path::get(Path::ROOT);
	$this->createDirectoryStructure($path, 5, 5);

	$this->assertFileExists($path, 'path exists');
	$this->assertFileExists($path . "/dir", "inner directory exists");

	DirectoryRecursor::purge($path);

	$this->assertFileNotExists($path);
}

private function createDirectoryStructure($basePath, $depth, $leaves) {
	for($d = 1; $d < $depth; $d++) {
		$path = "$basePath/";
		$path .= implode("/", array_fill(0, $d, "dir"));

		if(!is_dir($path)) {
			mkdir($path, 0775, true);
		}

		for($l = 1; $l <= $leaves; $l++) {
			$leaf = "$path/leaf-$l.file";

			file_put_contents($leaf, "File contents of leaf-$l");
		}
	}
}

/**
 *
 */
public function walkCallback($file, $iterator) {
	$path = $file->getPathname();
	return $path;
}

/**
 *
 */
public function hashCallback($file, $iterator) {
	if($file->isDir()) {
		return null;
	}

	$path = $file->getPathname();
	return md5($path) . md5_file($path);
}

}#