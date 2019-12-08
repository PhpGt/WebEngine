<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\WebEngine\Logic\DynamicPath;
use PHPUnit\Framework\TestCase;

class DynamicPathTest extends TestCase {
	public function testGetMissingKey() {
		$sut = new DynamicPath([
			"name" => uniqid(),
			"type" => uniqid(),
		]);
		self::assertNull($sut->get("nothing"));
	}

	public function testGetKey() {
		$nameValue = uniqid();
		$typeValue = uniqid();
		$sut = new DynamicPath([
			"name" => $nameValue,
			"type" => $typeValue,
		]);
		self::assertEquals($nameValue, $sut->get("name"));
		self::assertEquals($typeValue, $sut->get("type"));
	}
}