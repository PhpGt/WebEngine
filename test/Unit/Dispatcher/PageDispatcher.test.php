<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

class PageDispatcher_Test extends \PHPUnit_Framework_TestCase {

private $dispatcher;
private $tmp;
private $pageViewDir;

public function setUp() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();
	$this->pageViewDir = \Gt\Test\Helper::createTmpDir("/src/Page/View");

	$cfg = new \Gt\Core\ConfigObj();

	$request 	= $this->getMock("\Gt\Request\Request", null, [
		"/", $cfg,
	]);
	$response	= $this->getMock("\Gt\Response\Reponse", null);
	$apiFactory	= $this->getMock("\Gt\Api\ApiFactory", null, [
		$cfg
	]);
	$dbFactory	= $this->getMock("\Gt\Database\DatabaseFactory", null, [
		$cfg
	]);

	$this->dispatcher = new PageDispatcher(
		$request, $response, $apiFactory, $dbFactory);
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

private $uriList = [
	"/",
	"/index",
	"/one",
	"/two",
	"/three-four-five",
	"/directory/",
	"/directory/inner-file",
	"/directory/nested/double-inner-file",
	"/doubleName/doubleName",
];

public function data_uris() {
	$return = [];

	foreach ($this->uriList as $uri) {
		$return []= [$uri];
		if(strlen($uri) === 1
		|| substr($uri, -1) === "/") {
			continue;
		}

		$return []= [$uri . ".html"];
		$return []= [$uri . ".json"];
		$return []= [$uri . ".jpg"];
	}

	return $return;
}

public function testDispatcherCreated() {
	$this->assertInstanceOf("\Gt\Dispatcher\PageDispatcher", $this->dispatcher);
}

/**
 * @dataProvider data_uris
 */
public function testGetPathThrowsExceptionWhenNoDirectoryExists($uri) {
	$this->setExpectedException("\Gt\Response\NotFoundException");
	$this->dispatcher->getPath($uri . "/does/not/exist", $fixedUri);
}

/**
 * @dataProvider data_uris
 */
public function testGetPathFromUri($uri) {
	$filePath = $this->pageViewDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/index.test", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents($filePath, "dummy data");
	}

	$path = $this->dispatcher->getPath($uri, $fixedUri);
	$this->assertEquals($path, $dirname);
}

/**
 * @dataProvider data_uris
 */
public function testGetPathFixesUri($uri) {
	$uriRand = \Gt\Test\Helper::randomiseCase($uri);
	$filePath = $this->pageViewDir . $uriRand;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");
	$filePath = rtrim($filePath, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		$index = \Gt\Test\Helper::randomiseCase("index");
		file_put_contents($filePath . "/$index.test", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents($filePath, "dummy data");
	}

	$path = $this->dispatcher->getPath($uri, $fixedUri);

	if($filePath !== $dirname) {
		$this->assertNotEquals($fixedUri, $uri);
		$this->assertEquals(strtolower($fixedUri), strtolower($uri));
	}
}

/**
 * @dataProvider data_uris
 */
public function testLoadSourceFromPath($uri) {
	$filePath = $this->pageViewDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");
	$filePath = rtrim($filePath, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/index.test", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents($filePath, "dummy data");
	}

	$uriFile = basename($uri);

	$source = $this->dispatcher->loadSource($dirname, $uriFile);
	$source = str_replace("\n", "", $source);
	$this->assertEquals("dummy data", $source);
}

/**
 * @dataProvider data_uris
 */
public function testLoadSourceFromPathWithHeaderFooter($uri) {
	$filePath = $this->pageViewDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");
	$filePath = rtrim($filePath, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/_header.test", "header data");
		file_put_contents($filePath . "/_footer.test", "footer data");
		file_put_contents($filePath . "/index.test", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents(dirname($filePath) . "/_header.test", "header data");
		file_put_contents(dirname($filePath) . "/_footer.test", "footer data");
		file_put_contents($filePath, "dummy data");
	}

	$uriFile = basename($uri);

	$source = $this->dispatcher->loadSource($dirname, $uriFile);
	$source = str_replace("\n", "", $source);
	$this->assertEquals("header datadummy datafooter data", $source);
}

public function testCreateResponseContentFromHtml() {
	$html = "<!doctype html><h1>Test!</h1>";
	$responseContent = $this->dispatcher->createResponseContent($html);
	$this->assertInstanceOf("\Gt\Response\ResponseContent", $responseContent);
	$this->assertInstanceOf("\Gt\Response\Dom\Document", $responseContent);
}

public function testCreateResponseContentThrowsTypeException() {
	$this->setExpectedException(
		"\Gt\Core\Exception\InvalidArgumentTypeException");
	$notHtml = new \StdClass();
	$responseContent = $this->dispatcher->createResponseContent($notHtml);
}

/**
 * @dataProvider data_uris
 */
public function testGetFilenameRequestedFromUri($uri) {
	$path = $this->pageViewDir;
	$filename = $this->dispatcher->getFilename($uri, "index", $path);

	if(substr($uri, -1) === "/") {
		$this->assertEquals("index", $filename);
	}
	else {
		$this->assertEquals(basename($uri), $filename);
	}
}

// /**
//  * @dataProvider data_uris
//  */
// public function testDispatcherFixesUri($uri) {

// }

public function testDispatcherFlushes() {
	$html = "<!doctype html><h1>Test</h1>";
	$this->expectOutputRegex("/<!DOCTYPE html>.*<h1>Test<\/h1>.*<\/html>/s");
	$content = $this->dispatcher->createResponseContent($html);

	$content->flush();
}

}#