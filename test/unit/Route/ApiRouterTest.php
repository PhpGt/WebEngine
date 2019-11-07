<?php
namespace Gt\WebEngine\Test\Route;

use Gt\Http\Request;
use Gt\WebEngine\Route\ApiRouter;
use PHPUnit\Framework\TestCase;

class ApiRouterTest extends TestCase {
	public function testGetBaseViewLogicPath() {
		$request = self::createMock(Request::class);
		$sut = new ApiRouter($request, "");
		$path = $sut->getBaseViewLogicPath();
		self::assertEquals("/api", $path);
	}
}