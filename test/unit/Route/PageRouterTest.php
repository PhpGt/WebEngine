<?php
namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\Http\Uri;
use Gt\WebEngine\FileSystem\RequiredDirectoryNotFoundException;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Test\Helper\FunctionOverride\Override;
use PHPUnit\Framework\MockObject\MockObject;

class PageRouterTest extends RouterTestCase {
	public function testGetBaseViewLogicPath() {
		$uri = self::createMock(Uri::class);
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);

		$sut = new PageRouter(
			$request,
			"",
			"text/html"
		);
		$path = $sut->getBaseViewLogicPath();
		self::assertEquals("/page", $path);
	}

	/**
	 * @runInSeparateProcess
	 * @dataProvider dataUri
	 */
	public function testRedirectIndex(string $uriPath) {
		Override::replace("header", __DIR__);
		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn($uriPath);
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new PageRouter(
			$request,
			"",
			"text/html"
		);
		$sut->redirectInvalidPaths($uri);

		$expectedHeaderCalls = [];
		$lastPartOfUri = substr(
			$uri,
			strrpos($uri, "/") + 1
		);
		$uriNoLastPart = substr($uri, 0, strrpos($uri, "/"));
		if($lastPartOfUri === "index") {
			$expectedHeaderCalls []= ["Location: $uriNoLastPart", true, 303];
		}

		self::expectCallsToFunction("header", $expectedHeaderCalls);
	}

	public function testGetViewAssemblyNoPageDir() {
		$tmp = $this->getTmpDir("testGetViewAssemblyNoPageDir");
		touch("$tmp/composer.json");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/");
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new PageRouter(
			$request,
			$tmp,
			"text/html"
		);
		self::expectException(RequiredDirectoryNotFoundException::class);
		$sut->getViewAssembly();
	}

	public function testGetViewAssembly() {
		$tmp = $this->getTmpDir("testGetViewAssembly");
		touch("$tmp/composer.json");
		mkdir("$tmp/page");
		touch("$tmp/page/index.html");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/");
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new PageRouter(
			$request,
			$tmp,
			"text/html"
		);
		$assembly = $sut->getViewAssembly();

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".html", $assemblyPart);
		}
		self::assertNotNull($i);
	}

	public function testGetLogicAssembly() {
		$tmp = $this->getTmpDir("testGetLogicAssembly");
		touch("$tmp/composer.json");
		mkdir("$tmp/page");
		touch("$tmp/page/index.php");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/");

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);

		$sut = new PageRouter(
			$request,
			$tmp,
			"text/html"
		);
		$assembly = $sut->getLogicAssembly();

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".php", $assemblyPart);
		}

		self::assertNotNull($i);
	}

	public function testGetViewAssemblyDynamic() {
		$tmp = $this->getTmpDir("testGetLogicAssemblyDynamic");
		touch("$tmp/composer.json");
		mkdir("$tmp/page");
		mkdir("$tmp/page/item");
		touch("$tmp/page/item/@itemName.html");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/item/something");

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new PageRouter(
			$request,
			$tmp,
			"text/html"
		);
		$assembly = $sut->getViewAssembly();

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".html", $assemblyPart);
		}

		self::assertNotNull($i);
	}
}