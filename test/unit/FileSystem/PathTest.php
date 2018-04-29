<?php

namespace Gt\WebEngine\Test\FileSystem;

use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\Test\Helper\Helper;
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
		Helper::createChildDirectory($documentRoot, "src");

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
	public function testGetWwwDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"www",
			]),
			Path::getWwwDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetDataDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"data",
			]),
			Path::getDataDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetPageDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"page",
			]),
			Path::getPageDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetApiDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"api",
			]),
			Path::getApiDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetAssetDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"asset",
			]),
			Path::getAssetDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetScriptDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"script",
			]),
			Path::getScriptDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetStyleDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"style",
			]),
			Path::getStyleDirectory($documentRoot)
		);
	}

	/**
	 * @dataProvider dataProviderRootDirectoryExists
	 */
	public function testGetClassDirectory(string $documentRoot) {
		Helper::createSkeletonProject($documentRoot);
		self::assertEquals(
			implode(DIRECTORY_SEPARATOR, [
				$documentRoot,
				"src",
				"class",
			]),
			Path::getClassDirectory($documentRoot)
		);
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
		$fixed = Path::fixPathCase($childPathRandomised);
		self::assertDirectoryExists($fixed);
	}

	public function dataProviderRootDirectoryExists():array {
		$data = [];
		for($i = 0; $i < 25; $i++) {
			$path = Helper::getTmpDir();
			mkdir($path, 0775, true);
			$data []= [$path];
		}

		return $data;
	}
}
