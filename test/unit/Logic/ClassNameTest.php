<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\WebEngine\Logic\ClassName;
use PHPUnit\Framework\TestCase;

class ClassNameTest extends TestCase {
	public function testTransformDotPath() {
		$uri = "/example/.";
		$prefix = "\\Test\\App";
		$suffix = "PageOrApp";

		$transformed = ClassName::transformUriCharacters(
			$uri,
			$prefix,
			$suffix
		);

		self::assertEquals(
			"\\Test\\App\\ExamplePageOrApp",
			$transformed
		);
	}

	public function testTransformNested() {
		$uri = "/example/oneNest/twoNest/final";
		$prefix = "\\Test\\App";
		$suffix = "PageOrApp";

		$transformed = ClassName::transformUriCharacters(
			$uri,
			$prefix,
			$suffix
		);

		self::assertEquals(
			"\\Test\\App\\Example\\OneNest\\TwoNest\\FinalPageOrApp",
			$transformed
		);
	}

	public function testTransformDashed() {
		$uri = "/example/dashed-directory/thisIsCamelCase/another-dashed";
		$prefix = "\\Test\\App";
		$suffix = "PageOrApp";

		$transformed = ClassName::transformUriCharacters(
			$uri,
			$prefix,
			$suffix
		);
		self::assertEquals(
			"\\Test\\App\\Example\\DashedDirectory\\ThisIsCamelCase\\AnotherDashedPageOrApp",
			$transformed
		);
	}
}