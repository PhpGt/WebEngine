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
	// Temporary directory required to serve static files from.
	$this->tempDir = $this->createTempDir();
}

public function tearDown() {
	$this->cleanup($this->tempDir);
}

public function testStaticFileRequest() {
	// Check that Gateway understands strange queryStrings.
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/directory/image.jpg"));
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg?query=string"));
	$this->assertTrue(Gateway::isStaticFileRequest("/image.jpg?query=string"));
	$this->assertTrue(Gateway::isStaticFileRequest("/t.txt?a=1/2/3/"));	
}

public function testDynamicRequest() {
	$this->assertFalse(Gateway::isStaticFileRequest("/"));
	$this->assertFalse(Gateway::isStaticFileRequest("/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/directory/page"));
	$this->assertFalse(Gateway::isStaticFileRequest("/?query=string"));
	$this->assertFalse(Gateway::isStaticFileRequest("/example?query=string"));
	// Default pathinfo function is confused by this request. strtok is now 
	// used within Gateway.
	$this->assertFalse(Gateway::isStaticFileRequest(
		"/example?query=string&file=picture.jpg"));
}

public function testGetAbsoluteFilePath() {
	// Fake a document root, so the server object thinks there is an application
	// in use.
	$docRoot = "/dev/null/test-app/www";
	$_SERVER = ["DOCUMENT_ROOT" => $docRoot];

	foreach ($this->uriTestArray as $uri) {
		// Build expected full path to check against.
		$fullPath = $docRoot . $uri;

		$this->assertEquals(
			$fullPath,
			Gateway::getAbsoluteFilePath($uri)
		);
	}

	unset($_SERVER);
}

public function testServeStaticFile() {
	$expected = "";

	foreach ($this->uriTestArray as $uri) {
		$path = $this->getTempFilePath($uri);

		$expected .= self::DUMMY_CONTENT . " (from $uri).";
		Gateway::serveStaticFile($path);
	}
	$this->expectOutputString($expected);
}

public function data_brokenPathList() {
	$pathList = [];
	foreach ($this->uriTestArray as $uri) {
		$path = $this->getTempFilePath($uri);
		$pathList []= ["$path/"];
		$pathList []= ["/$path/"];
		$pathList []= ["?$path"];
		$pathList []= ["$path$path"];
		$pathList []= ["\\$path"];
	}
	return $pathList;
}

/**
 * @expectedException \Gt\Response\NotFoundException
 * @dataProvider data_brokenPathList
 */
public function testServeFakeFile($path) {
	Gateway::serveStaticFile($path);	
}

/**
 * Creates a directory in the system's tmp, stores the directory path in 
 * $this->tempDir.
 * 
 * @return string Temporary directroy path.
 */
private function createTempDir() {
	$tmp = sys_get_temp_dir();
	$this->tempDir = tempnam($tmp, self::TEMP_PREFIX);
	if(file_exists($this->tempDir)) {
		$this->cleanup($this->tempDir);
	}

	mkdir($this->tempDir);
	return $this->tempDir;
}

/**
 * Recursive function to empty and remove a whole directory.
 * 
 * @param string $dir Path to directory to remove.
 * @return bool True if directory is successfully removed, otherwise false.
 */
private function cleanup($dir) {
	if(empty($dir)) {
		return true;
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

/**
 * Creates a temp file in the current tempDir location named with the provided
 * uri parameter. Content is filled with dummy content suffixed with the uri.
 * 
 * @param string $uri Temp directory path suffix.
 * @return string Absolute path to newly created file.
 */
private function getTempFilePath($uri) {
	$tempDir = $this->createTempDir();
	$path = $tempDir . $uri;

	if(!is_dir(dirname($path)) ) {
		mkdir(dirname($path), 0775, true);
	}
	file_put_contents($path, self::DUMMY_CONTENT . " (from $uri).");

	return $path;
}

}#