<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;

use \Gt\Response\Headers;

class Gateway_Test extends \PHPUnit_Framework_TestCase {

const DUMMY_CONTENT = "!!! DUMMY CONTENT !!!";
const TEMP_PREFIX = "gt-test-phpgt-tmp";

private $tempDir;

public function setUp() {
	$this->createTempDir();
	$_SERVER["DOCUMENT_ROOT"] = $this->tempDir . "/www";
	if(!is_dir($_SERVER["DOCUMENT_ROOT"])) {
		mkdir($_SERVER["DOCUMENT_ROOT"], 0775, true);
	}
}

public function tearDown() {
	$tmp = sys_get_temp_dir();
	$cwd = getcwd();

	chdir($tmp);
	foreach (glob(self::TEMP_PREFIX . "*") as $file) {
		\Gt\Test\Helper::cleanup("$tmp/$file");
	}

	chdir($cwd);
}

/**
 * Data provider that gives back a number of random URIs with random or no
 * file extension, with a random number of directory nesting.
 */
public function data_randomUris() {
	$data = [["/test"], ["/test.html"], ["/test.json"]];
	$numPaths = 10;
	$extArray = [
		"",
		".js",
		".json",
		".xml",
		".html",
		".jpg",
		".php",
		".txt",
		".gif",
	];

	for($i = 0; $i < $numPaths; $i++) {
		$extRandom = rand(0, count($extArray) - 1);
		$nestCount = rand(1, 15);

		$uri = "";

		for($j = 0; $j < $nestCount; $j++) {
			$uri .= "/" . uniqid();
		}

		$data []= [
			$uri . $extArray[$extRandom],
		];
	}

	return $data;
}

/**
 * A file that exists in www should be served, no matter what extension.
 *
 * @dataProvider data_randomUris
 */
public function testServeStaticFile($uri) {
	$this->getTempFilePath($uri);
	Gateway::serve($uri);
	$this->expectOutputString(self::DUMMY_CONTENT . " (from $uri).");
}

/**
 * A file that doesn't exist in www should create a new instance of the passed
 * in class.
 *
 * @dataProvider data_randomUris
 */
public function testServeDynamicFile($uri) {
	$this->getTempFilePath($uri, true);
	$output = Gateway::serve($uri, "\StdClass");

	$this->assertInstanceOf("\StdClass", $output);
}

/**
 * @dataProvider data_randomUris
 */
public function testSendsCorrectHeadersServeStaticFile($uri) {
	$filePath = $this->getTempFilePath($uri);

	$mime = Server::$contentTypeDefault;
	$ext = pathinfo($filePath, PATHINFO_EXTENSION);

	if(isset(Server::$contentType[$ext])) {
		$mime = Server::$contentType[$ext];
	}
	else {
		$finfo = new \Finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo->file($filePath);
	}

	$this->expectOutputString(self::DUMMY_CONTENT . " (from $uri).");
	Gateway::serveStaticFile($filePath);

	$headers = Headers::getAll();
	$this->assertInternalType("array", $headers);
	$this->assertArrayHasKey("Content-type", $headers);
	$this->assertEquals($headers["Content-type"], $mime);
}

/**
 * Creates a directory in the system's tmp, stores the directory path in
 * $this->tempDir.
 *
 * @return string Temporary directroy path.
 */
private function createTempDir() {
	$tmp = sys_get_temp_dir();
	$this->tempDir = $tmp . "/" . uniqid(self::TEMP_PREFIX);

	mkdir($this->tempDir);
	return $this->tempDir;
}

/**
 * Creates a temp file in the current tempDir location named with the provided
 * uri parameter. Content is filled with dummy content suffixed with the uri.
 *
 * @param string $uri Temp directory path suffix.
 * @return string Absolute path to newly created file.
 */
private function getTempFilePath($uri, $skipCreating = false) {
	$path = $_SERVER["DOCUMENT_ROOT"] . $uri;

	if(!$skipCreating) {
		if(!is_dir(dirname($path)) ) {
			mkdir(dirname($path), 0775, true);
		}
		file_put_contents($path, self::DUMMY_CONTENT . " (from $uri).");
	}

	return $path;
}

}#