<?php
namespace Gt\WebEngine\FileSystem;

use DirectoryIterator;

class Path {
	protected static $appRoot;

	public static function getApplicationRootDirectory(string $innerDirectory):string {
		$directoryWalker = new DirectoryWalker($innerDirectory);

		return self::fixPathCase(
			$directoryWalker->findParentContaining("composer.json")
		);
	}

	public static function getGtRootDirectory():string {
		$directoryWalker = new DirectoryWalker(__DIR__);
		return self::fixPathCase(
			$directoryWalker->findParentContaining("src")
		);
	}

	public static function getWwwDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"www",
		]);
	}

	public static function getDataDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"data",
		]);
	}

	public static function getPageDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"page",
		]);
	}
	public static function getApiDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"api",
		]);
	}

	public static function getAssetDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"asset",
		]);
	}

	public static function getScriptDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"script",
		]);
	}

	public static function getStyleDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"style",
		]);
	}

	public static function getClassDirectory(string $appRoot = null):string {
		$appRoot = self::defaultApplicationRoot($appRoot);
		return implode(DIRECTORY_SEPARATOR, [
			self::getApplicationRootDirectory($appRoot),
			"class",
		]);
	}

	public static function fixPathCase(string $path):string {
// TODO: This breaks within a "jailed" Linux user. See https://github.com/PhpGt/WebEngine/issues/260
// Use a base directory of "getApplicationRootDirectory", and have this check for a constant
// defined in go.php?
		$output = "";
		if(DIRECTORY_SEPARATOR === "/") {
			$output .= DIRECTORY_SEPARATOR;
		}
		$path = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $path);
		$pathParts = explode(DIRECTORY_SEPARATOR, $path);

		foreach($pathParts as $directory) {
			$currentSearchPath = $output;
			$currentSearchPath .= $directory;

// If the directory exists without its path being changed, use that and continue to next child.
			if(is_dir($currentSearchPath)) {
				$output = "$currentSearchPath";

				if(strlen($output) > 1) {
					$output .= DIRECTORY_SEPARATOR;
				}
				continue;
			}

			$iterator = new DirectoryIterator($output);
			$foundMatch = false;
			foreach($iterator as $fileInfo) {
				$fileName = $fileInfo->getFilename();
				if(strtolower($fileName) === strtolower($directory)) {
					$output .= "$fileName" . DIRECTORY_SEPARATOR;

					$foundMatch = true;
					break;
				}
			}

			if(!$foundMatch) {
				throw new PathNotFound($path);
			}
		}

		$output = rtrim($output, DIRECTORY_SEPARATOR);
		return $output;
	}

	protected static function defaultApplicationRoot(string $default = null) {
		if(!is_null($default)) {
			self::$appRoot = $default;
			return $default;
		}

		if(empty(self::$appRoot)) {
			self::$appRoot = getcwd();

			if(!is_file(implode(DIRECTORY_SEPARATOR, [
				self::$appRoot,
				"vendor",
				"autoload.php",
			]))) {
				throw new ApplicationRootDirectoryNotSetException();
			}
		}

		return self::$appRoot;
	}
}