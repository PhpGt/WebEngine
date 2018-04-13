<?php
namespace Gt\WebEngine\Test\Helper;

class Helper {
	const TMP_PREFIX = "phpgt-webengine";
	const ROOT_DIRECTORIES = [
		"data",
		"src",
		"vendor",
		"www",
	];
	const SRC_DIRECTORIES = [
		"api",
		"asset",
		"class",
		"page",
		"query",
		"script",
		"style",
	];

	/**
	 * Provides the absolute path to a directory that is unique to the test case.
	 */
	public static function getTmpDir():string {
		return implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"webengine",
			uniqid()
		]);

	}

	public static function deleteDir(string $dir) {
		exec("rm -rf $dir");
	}

	public static function createNestedChildrenDirectories(string $path, int $depth = 10):string {
		for($i = 0; $i < $depth; $i++) {
			$path .= DIRECTORY_SEPARATOR;
			$path .= uniqid("child-dir", true);
		}

		mkdir($path, 0775, true);
		return $path;
	}

	public static function createChildDirectory(string $path, string $childName):string {
		$dir = implode("/", [
			$path,
			$childName,
		]);
		mkdir($dir);
		return $dir;
	}

	public static function randomiseCase(string $string):string {
		for($i = 0, $len = strlen($string); $i < $len; $i++) {
			$string[$i] = mt_rand(0, 1)
				? strtoupper($string[$i])
				: strtolower($string[$i]);
		}

		return $string;
	}

	public static function createSkeletonProject($documentRoot):void {
		foreach(self::ROOT_DIRECTORIES as $directory) {
			mkdir("$documentRoot/$directory", 0775, true);
		}

		foreach(self::SRC_DIRECTORIES as $directory) {
			mkdir("$documentRoot/src/$directory", 0775, true);
		}
	}
}