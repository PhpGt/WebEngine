<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class Gateway_Test extends \PHPUnit_Framework_TestCase {

const DUMMY_CONTENT = "!!! DUMMY CONTENT !!!";
const TEMP_PREFIX = "gt-test-phpgt-tmp";

private $uriTestArray = [
	"/test.txt",
	"/Script/test-app.js",
	"/Script/test-app.js?v=123",
	"/Script/Lib/jquery.js",
	"/Asset/Img/Cat.jpg",
];
private $tempDir;

public function setUp() {
	$this->tempDir = $this->createTempDir();
}

public function tearDown() {
	$this->cleanup($this->tempDir);
}

public function testStaticFileRequest() {
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/directory/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg?query=string"));
}

public function testDynamicRequest() {
	$this->assertFalse(Gateway::isStaticFileRequest("/"));
	$this->assertFalse(Gateway::isStaticFileRequest("/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/directory/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/?query=string"));
	$this->assertFalse(Gateway::isStaticFileRequest("/example?query=string"));
	$this->assertFalse(Gateway::isStaticFileRequest(
		"/example?query=string&file=picture.jpg"));
}

public function testGetAbsoluteFilePath() {
	// Fake a document root, so the server object thinks there is an application
	// in use.
	$docRoot = "/dev/null/test-app/www";
	$_SERVER = ["DOCUMENT_ROOT" => $docRoot];

	foreach ($this->uriTestArray as $uri) {
		$this->assertEquals(
			$docRoot . $uri,
			Gateway::getAbsoluteFilePath($uri)
		);
	}
}

public function testServeStaticFile() {
	foreach ($this->uriTestArray as $uri) {
		$path = $this->getTempFilePath($uri);

		Gateway::serveStaticFile($path);

		// TODO: Test stdout...
		$this->assertEquals(self::DUMMY_CONTENT, trim(fread(STDOUT, 1024)));
		fclose($fh);
	}
}

private function createTempDir() {
	$tmp = sys_get_temp_dir();
	$this->tempDir = tempnam($tmp, self::TEMP_PREFIX);
	if(file_exists($this->tempDir)) {
		$this->cleanup($this->tempDir);
	}

	mkdir($this->tempDir);
	return $this->tempDir;
}

private function cleanup($dir) {
	if(empty($dir)) {
		return;
	}

	if(is_file($dir)) {
		return unlink($dir);
	}

	$files = array_diff(scandir($dir), array('.','..')); 
	foreach ($files as $file) { 
		if(is_dir("$dir/$file")) {
			$this->cleanup("$dir/$file");
		}
		else {
			unlink("$dir/$file");
		}
	} 
	
	return rmdir($dir); 
}

private function getTempFilePath($uri) {
	$tempDir = $this->createTempDir();
	$path = $tempDir . $uri;

	if(!is_dir(dirname($path)) ) {
		mkdir(dirname($path), 0775, true);
	}
	file_put_contents($path, self::DUMMY_CONTENT);

	return $path;
}

}#