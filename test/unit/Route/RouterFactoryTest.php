<?php
namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\Http\Uri;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Route\RoutingException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterFactoryTest extends TestCase {
	public function testCreateInvalidAcceptHeader() {
		$uri = self::createMock(Uri::class);
		/** @var Request|MockObject $request */
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("no/matches");
		$request->method("getUri")
			->willReturn($uri);

		self::expectException(RoutingException::class);
		self::expectExceptionMessage("Accept header has no route: no/matches");
		$sut = new RouterFactory();
		$sut->create($request, "");
	}

	public function testCreateEmptyAcceptHeader() {
		$uri = self::createMock(Uri::class);

		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("");
		$request->method("getUri")
			->willReturn($uri);

// The accept header should default to text/html if none is provided.
		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			PageRouter::class,
			$router
		);
	}

	public function testCreateTextHtmlAcceptHeader() {
		$uri = self::createMock(Uri::class);
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("text/html");
		$request->method("getUri")
			->willReturn($uri);

		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			PageRouter::class,
			$router
		);
	}

	public function testCreateApplicationJsonHeader() {
		$uri = self::createMock(Uri::class);
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("application/json");
		$request->method("getUri")
			->willReturn($uri);

		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			ApiRouter::class,
			$router
		);
	}
}