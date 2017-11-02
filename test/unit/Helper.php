<?php
namespace Gt\Test;

class Helper {
	const TMP_PREFIX = "phpgt-webengine";

	/**
	 * Provides the absolute path to a directory that is unique to the test case.
	 */
	public static function getTempDirectory():string {
		$tmp = sys_get_temp_dir();
		$unique = uniqid(self::TMP_PREFIX, true);
		$path = "$tmp/$unique";
		return $path;
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
}