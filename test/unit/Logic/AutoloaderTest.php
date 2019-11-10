<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\WebEngine\Logic\Autoloader;
use Gt\WebEngine\Logic\AutoloaderException;
use Gt\WebEngine\Test\Helper\ThisClassShouldNotBeLoadedPage;
use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase {
	public function testAutoloadFindsCommonPage() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"dynamic-uris",
			"www",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		$autoloader->autoload(
			"\\Test\\App\\Page\\_CommonPage"
		);

		self::assertTrue(class_exists(
			"\\Test\\App\\Page\\_CommonPage",
			false
		));
	}

	public function testAutoloadFindsNestedCommonPage() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"dynamic-uris",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		$autoloader->autoload(
			"\\Test\\App\\Page\\dir\\nested\\_CommonPage"
		);

		self::assertTrue(class_exists(
			"\\Test\\App\\Page\\dir\\nested\\_CommonPage",
			false
		));
	}

	public function testAutoloadWithInvalidSuffix() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"dynamic-uris",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);
		$autoloader->autoload("\\Test\\App\\ExampleThing");
		self::assertFalse(class_exists(
			"\\Test\\App\\ExampleThing",
			false
		));
	}

	public function testAutoloadOtherNamespace() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"dynamic-uris",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);
		$autoloader->autoload(ThisClassShouldNotBeLoadedPage::class);
		self::assertFalse(class_exists(
				ThisClassShouldNotBeLoadedPage::class,
				false
		));
	}

	public function testAutoloadFindsApiClass() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"autoloading",
			"www",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		$autoloader->autoload(
			"\\Test\\App\\Api\\ServiceThingApi"
		);

		self::assertTrue(class_exists(
			"\\Test\\App\\Api\\ServiceThingApi",
			false
		));
	}

	public function testAutoloadFindsDoublyNestedApiClass() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"autoloading",
			"www",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		$autoloader->autoload(
			"\\Test\\App\\Api\\OneNest\\TwoNest\\DoublyNestedApi"
		);

		self::assertTrue(class_exists(
			"\\Test\\App\\Api\\OneNest\\TwoNest\\DoublyNestedApi",
			false
		));
	}

	public function testAutoloadFindsDoublyNestedWrongNamedApiClassFile() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"autoloading",
			"www",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		self::expectException(AutoloaderException::class);
		self::expectExceptionMessage("File path is not correct for when autoloading class 'Test\\App\\Api\\OneNest\\TwoNest\\WrongNameApi'");
		$autoloader->autoload(
			"\\Test\\App\\Api\\OneNest\\TwoNest\\WrongNameApi"
		);
	}
}