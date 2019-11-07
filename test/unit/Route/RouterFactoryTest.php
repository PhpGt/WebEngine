<?php
namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Route\RoutingException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterFactoryTest extends TestCase {
	public function testCreateInvalidAcceptHeader() {
		/** @var Request|MockObject $request */
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("no/matches");

		self::expectException(RoutingException::class);
		self::expectExceptionMessage("Accept header has no route: no/matches");
		$sut = new RouterFactory();
		$sut->create($request, "");
	}

	public function testCreateEmptyAcceptHeader() {
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("");

// The accept header should default to text/html if none is provided.
		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			PageRouter::class,
			$router
		);
	}

	public function testCreateTextHtmlAcceptHeader() {
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("text/html");

		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			PageRouter::class,
			$router
		);
	}

	public function testCreateApplicationJsonHeader() {
		$request = self::createMock(Request::class);
		$request->method("getHeaderLine")
			->with("accept")
			->willReturn("application/json");

		$sut = new RouterFactory();
		$router = $sut->create($request, "");
		self::assertInstanceOf(
			ApiRouter::class,
			$router
		);
	}
}