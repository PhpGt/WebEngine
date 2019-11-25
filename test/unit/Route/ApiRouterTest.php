<?php

namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\WebEngine\FileSystem\RequiredDirectoryNotFoundException;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Test\Helper\FunctionOverride\Override;
use PHPUnit\Framework\MockObject\MockObject;

class ApiRouterTest extends RouterTestCase {
	public function testGetBaseViewLogicPath() {
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, "");
		$path = $sut->getBaseViewLogicPath();
		self::assertEquals("/api", $path);
	}

	/**
	 * @runInSeparateProcess
	 * @dataProvider dataUri
	 */
	public function testRedirectIndex(string $uri) {
		Override::replace("header", __DIR__);
		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, "");
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

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, $tmp);
		self::expectException(RequiredDirectoryNotFoundException::class);
		$sut->getViewAssembly("/");
	}

	public function testGetViewAssembly() {
		$tmp = $this->getTmpDir("testGetViewAssembly");
		touch("$tmp/composer.json");
		mkdir("$tmp/api");
		touch("$tmp/api/example.json");

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, $tmp);
		$assembly = $sut->getViewAssembly("/example");

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

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, $tmp);
		$assembly = $sut->getLogicAssembly("/example");

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
		touch("$tmp/api/@itemName.json");

		/** @var MockObject|Request $request */
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, $tmp);
		$assembly = $sut->getViewAssembly("/item/something");

		$i = null;

		foreach($assembly as $i => $assemblyPart) {
			self::assertFileExists($assemblyPart);
			self::assertStringEndsWith(".json", $assemblyPart);
		}

		self::assertNotNull($i);
	}
}