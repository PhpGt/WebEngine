<?php

namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\Http\Uri;
use Gt\WebEngine\Dispatch\Dispatcher;
use Gt\WebEngine\FileSystem\RequiredDirectoryNotFoundException;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Test\Helper\FunctionOverride\Override;
use PHPUnit\Framework\MockObject\MockObject;

class ApiRouterTest extends RouterTestCase {
	public function testGetBaseViewLogicPath() {
		/** @var MockObject|Uri $uri */
		$uri = self::createMock(Uri::class);

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new ApiRouter(
			$request,
			"",
			"application/json"
		);
		$path = $sut->getBaseViewLogicPath();
		self::assertEquals("/api", $path);
	}

	/**
	 * @runInSeparateProcess
	 * @dataProvider dataUri
	 */
	public function testRedirectIndex(string $uri) {
		Override::replace("header", __DIR__);
		$uri = self::createMock(Uri::class);
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new ApiRouter(
			$request,
			"",
			"application/json"
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

	public function testGetViewAssemblyNoApiDir() {
		$tmp = $this->getTmpDir("testGetViewAssemblyNoApiDir");
		touch("$tmp/composer.json");

		$uri = self::createMock(Uri::class);
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);

		$sut = new ApiRouter(
			$request,
			$tmp,
			"application/json"
		);
		self::expectException(RequiredDirectoryNotFoundException::class);
		$sut->getViewAssembly($uri);
	}

	public function testGetViewAssembly() {
		$tmp = $this->getTmpDir("testGetViewAssembly");
		touch("$tmp/composer.json");
		mkdir("$tmp/api");
		touch("$tmp/api/example.json");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/example");
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);

		$sut = new ApiRouter(
			$request,
			$tmp,
			"application/json"
		);
		$assembly = $sut->getViewAssembly($uri);

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".json", $assemblyPart);
		}
		self::assertNotNull($i);
	}

	public function testGetLogicAssembly() {
		$tmp = $this->getTmpDir("testGetLogicAssembly");
		touch("$tmp/composer.json");
		mkdir("$tmp/api");
		touch("$tmp/api/example.php");

		/** @var MockObject|Uri $uri */
		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/example");
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);

		$sut = new ApiRouter(
			$request,
			$tmp,
			"application/json"
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
		mkdir("$tmp/api");
		mkdir("$tmp/api/item");
		touch("$tmp/api/item/@itemName.json");

		$uri = self::createMock(Uri::class);
		$uri->method("getPath")
			->willReturn("/item/something");
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$request->method("getUri")
			->willReturn($uri);
		$sut = new ApiRouter(
			$request,
			$tmp,
			"application/json"
		);
		$assembly = $sut->getViewAssembly();

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".json", $assemblyPart);
		}

		self::assertNotNull($i);
	}
}