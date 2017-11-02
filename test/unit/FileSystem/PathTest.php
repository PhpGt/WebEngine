<?php

namespace Gt\Test\FileSystem;

use Gt\FileSystem\Path;
use Gt\Test\Helper;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase {
	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetApplicationRootDirectory($documentRoot) {
		$childPath = Helper::createChildDirectories(
			$documentRoot,
			rand(2, 20)
		);
		$srcDirectory = implode(DIRECTORY_SEPARATOR, [
			$documentRoot,
			"src",
		]);
		mkdir($srcDirectory);

		$actualRoot = Path::getApplicationRootDirectory($childPath);

		self::assertEquals($documentRoot, $actualRoot);
		self::assertDirectoryExists($actualRoot);
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
