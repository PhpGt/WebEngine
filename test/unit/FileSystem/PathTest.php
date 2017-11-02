<?php

namespace Gt\Test\FileSystem;

use Gt\FileSystem\Path;
use Gt\Test\Helper;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase {
	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetApplicationRootDirectory(string $documentRoot) {
		$childPath = Helper::createNestedChildrenDirectories(
			$documentRoot,
			rand(2, 20)
		);
		$srcDirectory = Helper::createChildDirectory($documentRoot, "src");

		$actualRoot = Path::getApplicationRootDirectory($childPath);

		self::assertEquals($documentRoot, $actualRoot);
		self::assertDirectoryExists($actualRoot);
	}

	public function testGetGtRootDirectory() {
		$expectedGtRootDirectory = realpath(__DIR__ . "/../../..");
		$actualRootDirectory = Path::getGtRootDirectory();

		self::assertEquals($expectedGtRootDirectory, $actualRootDirectory);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testFixPathCase(string $documentRoot) {
		$childPath = Helper::createNestedChildrenDirectories(
			$documentRoot,
			rand(3, 15)
		);
		$childPathRandomised = Helper::randomiseCase($childPath);

		self::assertNotEquals($childPath, $childPathRandomised);
		self::assertEquals(strtolower($childPath), strtolower($childPathRandomised));
		self::assertDirectoryNotExists($childPathRandomised);

		$fixed = Path::fixPathCase($childPathRandomised);
		self::assertEquals($childPath, $fixed);
		self::assertDirectoryExists($fixed);
	}

	public function dataProviderRootDirectoryExists():array {
		$data = [];
		for($i = 0; $i < 25; $i++) {
			$path = Helper::getTempDirectory();
			mkdir($path, 0775, true);
			$data []= [$path];
		}

		return $data;
	}
}
