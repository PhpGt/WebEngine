<?php
namespace Gt\WebEngine\FileSystem;

use DirectoryIterator;

class Path {
	public static function getApplicationRootDirectory(string $innerDirectory = null):string {
		if(is_null($innerDirectory)) {
			$innerDirectory = $_SERVER["DOCUMENT_ROOT"];
		}

		$directoryWalker = new DirectoryWalker($innerDirectory);

		return self::fixPathCase(
			$directoryWalker->findParentContaining("src")
		);
	}

	public static function getGtRootDirectory():string {
		$directoryWalker = new DirectoryWalker(__DIR__);
		return self::fixPathCase(
			$directoryWalker->findParentContaining("src")
		);
	}

	public static function getSrcDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src",
		]);
	}

	public static function getWwwDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"www",
		]);
	}

	public static function getDataDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"data",
		]);
	}

	public static function getPageDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/page",
		]);
	}
	public static function getApiDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/api",
		]);
	}

	public static function getAssetDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/asset",
		]);
	}

	public static function getScriptDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/script",
		]);
	}

	public static function getStyleDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/style",
		]);
	}

	public static function getClassDirectory(string $documentRoot = null):string {
		return implode("/", [
			self::getApplicationRootDirectory($documentRoot),
			"src/class",
		]);
	}

	public static function getChildOfSrcDirectory(string $name):string {
		return self::fixPathCase(implode("/", [
			self::getSrcDirectory(),
			$name,
		]));
	}

	public static function fixPathCase(string $path):string {
// TODO: This breaks within a "jailed" Linux user. See https://github.com/PhpGt/WebEngine/issues/260
// Use a base directory of "getApplicationRootDirectory", and have this check for a constant
// defined in go.php?
		$output = "/";
		$pathParts = explode("/", $path);

		foreach($pathParts as $directory) {
			$currentSearchPath = $output;
			$currentSearchPath .= $directory;

// If the directory exists without its path being changed, use that and continue to next child.
			if(is_dir($currentSearchPath)) {
				$output = "$currentSearchPath";

				if(strlen($output) > 1) {
					$output .= "/";
				}
				continue;
			}

			$iterator = new DirectoryIterator($output);
			$foundMatch = false;
			foreach($iterator as $fileInfo) {
				$fileName = $fileInfo->getFilename();
				if(strtolower($fileName) === strtolower($directory)) {
					$output .= "$fileName/";

					$foundMatch = true;
					break;
				}
			}

			if(!$foundMatch) {
				throw new PathNotFound($path);
			}
		}

		$output = rtrim($output, "/");
		return $output;
	}
}