<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

class PageDispatcher_Test extends \PHPUnit_Framework_TestCase {

private $dispatcher;
private $tmp;
private $pageDir;

private $request;
private $response;
private $api;
private $session;
private $data;
private $appNamespace = "TestApp";

public function setUp() {
	if(!defined("APP_NAMESPACE")) {
		define("APP_NAMESPACE", "TestApp");
	}
	$this->tmp = \Gt\Test\Helper::createTmpDir();
	$this->pageDir = \Gt\Test\Helper::createTmpDir("/src/Page");

	$cfg = new \Gt\Core\ConfigObj();
	// $cfg->client_compiled = false;

	$this->request 		= $this->getMock("\Gt\Request\Request", ["getType"], [
		"/", $cfg,
	]);
	$this->request->expects($this->any())
		->method("getType")
		->will($this->returnValue(\Gt\Request\Request::TYPE_PAGE)
	);
	$this->response		= $this->getMock("\Gt\Response\Reponse",
		[
			"getConfigOption",
			"getCode",
		]
	);
	$responseCode = $this->getMock("\Gt\Response\ResponseCode",
		["getType"]
	);
	$responseCode->expects($this->any())
		->method("getType")
		->will($this->returnValue(\Gt\Response\ResponseCode::TYPE_SUCCESS));

	$this->response->expects($this->any())
		->method("getConfigOption")
		->will($this->returnValue(false));

	$this->response->expects($this->any())
		->method("getCode")
		->will($this->returnValue($responseCode));

	$this->response->code = $responseCode;

	$this->response->config = null;
	$this->response->production = false;

	$this->api	= $this->getMock("\Gt\Api\Api", null, [
		$cfg
	]);

	$this->session = $this->getMock("\Gt\Session\Session", null, [$cfg]);
	$this->data = $this->getMock("\Gt\Data\Data");

	$this->dispatcher = new PageDispatcher(
		$this->appNamespace,
		$this->request,
		$this->response,
		$this->api,
		$this->session,
		$this->data
	);
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
	$filePath = $this->pageDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/index.html", "dummy data");
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
	$filePath = $this->pageDir . $uriRand;
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
		file_put_contents($filePath . "/$index.html", "dummy data");
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

public function testGetPathThrowsException() {
	$this->setExpectedException("\Gt\Response\NotFoundException");
	$uri = "/does-not-exist";
	$path = $this->dispatcher->getPath($uri, $fixedUri);
}

/**
 * @dataProvider data_uris
 */
public function testLoadSourceFromPath($uri) {
	$filePath = $this->pageDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");
	$filePath = rtrim($filePath, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/index.html", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents($filePath . ".html", "dummy data");
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
	$filePath = $this->pageDir . $uri;
	$dirname = (substr($filePath, -1) === "/")
		? $filePath
		: dirname($filePath);
	$dirname = rtrim($dirname, "/");
	$filePath = rtrim($filePath, "/");

	if(!is_dir($dirname) ) {
		mkdir($dirname, 0775, true);
	}

	if(is_dir($filePath)) {
		file_put_contents($filePath . "/_header.html", "header data");
		file_put_contents($filePath . "/_footer.html", "footer data");
		file_put_contents($filePath . "/index.html", "dummy data");
		$uri .= "index";
	}
	else {
		file_put_contents(dirname($filePath) . "/_header.html", "header data");
		file_put_contents(dirname($filePath) . "/_footer.html", "footer data");
		file_put_contents($filePath . ".html", "dummy data");
	}

	$uriFile = basename($uri);

	$source = $this->dispatcher->loadSource($dirname, $uriFile);
	$source = str_replace("\n", "", $source);
	$this->assertEquals("header datadummy datafooter data", $source);
}

public function testCreateResponseContentFromHtml() {
	$html = "<!doctype html><h1>Test!</h1>";
	$responseContent = $this->dispatcher->createResponseContent($html, null);
	$this->assertInstanceOf("\Gt\Response\ResponseContent", $responseContent);
	$this->assertInstanceOf("\Gt\Dom\Document", $responseContent);
}

public function testCreateResponseContentThrowsTypeException() {
	$this->setExpectedException(
		"\Gt\Core\Exception\InvalidArgumentTypeException");
	$notHtml = new \StdClass();
	$responseContent = $this->dispatcher->createResponseContent($notHtml, null);
}

/**
 * @dataProvider data_uris
 */
public function testGetFilenameRequestedFromUri($uri) {
	if(substr($uri, -1) === "/") {
		if(strlen($uri) > 1) {
			$dirPath = $this->pageDir . $uri;

			if(!is_dir($dirPath)) {
				mkdir($dirPath, 0775, true);
			}
		}

		$filename = $this->dispatcher->getFilename($uri, "index", $fullUri);
		$this->assertEquals("index", $filename);
		$this->assertEquals($uri . "/index", $fullUri);
	}
	else {
		$filename = $this->dispatcher->getFilename($uri, "index", $fullUri);
		$this->assertEquals(basename($uri), $filename);
		$this->assertEquals($uri, $fullUri);
	}
}

/**
 * @dataProvider data_uris
 */
public function testDispatcherProcessFixesUri($uri) {
	if($uri === "/") {
		// Nothing to correct when empty URI
		return;
	}

	$uriRand = \Gt\Test\Helper::randomiseCase($uri);
	$filePath = $this->pageDir . $uri;
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
		file_put_contents($filePath . "/$index.html", "dummy data ($uri)");
		$uri .= "index";
	}
	else {
		file_put_contents($filePath, "dummy data ($uri)");
	}

	for($force = 0; $force <= 1; $force++) {
		$this->request->forceExtension = !!$force;
		$this->request->indexFilename = "index";
		$this->request->uri = $uriRand;

		$this->dispatcher = new PageDispatcher(
			$this->appNamespace,
			$this->request,
			$this->response,
			$this->api,
			$this->session,
			$this->data
		);

		$fixedUri = $this->dispatcher->process();
		$this->assertInternalType("string", $fixedUri);
		$this->assertEquals(
			strtok(strtolower($fixedUri), "."),
			strtok(strtolower($uriRand), ".")
		);
	}
}

public function testDispatcherFlushes() {
	$html = "<!doctype html><h1>Test</h1>";
	$this->expectOutputRegex("/<!DOCTYPE html>.*<h1>Test<\/h1>.*<\/html>/s");
	$content = $this->dispatcher->createResponseContent($html, null);

	$content->flush();
}

public function testDispatcherProcessFlushes() {
	// All we need to know is that the content flushes, actual computation is
	// tested in other test cases.
	$this->expectOutputRegex("/.*<h1>Test<\/h1>.*/s");

	// $request = new \Gt\Core\Obj([], true, true);
	// $request->forceExtension = true;
	// $request->indexFilename = "index";
	// $request->uri = "/test.html";
	// $request->type = \Gt\Request\Request::TYPE_PAGE;
	$cfg = new \Gt\Core\ConfigObj();
	$cfg->forceExtension = false;
	//////
	$request 		= $this->getMock("\Gt\Request\Request", ["getType"], [
		"/test", $cfg,
	]);
	$request->expects($this->any())
		->method("getType")
		->will($this->returnValue(\Gt\Request\Request::TYPE_PAGE)
	);
	/////

	file_put_contents($this->pageDir . "/test.html", "<h1>Test</h1>");

	$this->dispatcher = new PageDispatcher(
		$this->appNamespace,
		$request,
		$this->response,
		$this->api,
		$this->session,
		$this->data
	);

	$this->dispatcher->process();
}

}#