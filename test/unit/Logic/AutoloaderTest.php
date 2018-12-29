<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\WebEngine\Logic\Autoloader;
use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase {
	public function testAutoloadFindsCommonPage() {
		$docRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"CommonAutoloader",
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
			"CommonAutoloader",
		]);

		$autoloader = new Autoloader(
			"Test\\App",
			realpath($docRoot)
		);

		$autoloader->autoload(
			"\\Test\\App\\Page\\subdir\\_CommonPage"
		);

		self::assertTrue(class_exists(
			"\\Test\\App\\Page\\SubDir\\_CommonPage",
			false
		));
	}
}