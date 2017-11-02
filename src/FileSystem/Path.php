<?php
namespace Gt\FileSystem;

use DirectoryIterator;

class Path {
	public static function getApplicationRootDirectory(string $documentRoot = null):string {
		if(is_null($documentRoot)) {
			$documentRoot = $_SERVER["DOCUMENT_ROOT"];
		}

		$path = getcwd();

		if(!empty($documentRoot)) {
			$path = dirname($documentRoot);
		}

		$directoryWalker = new DirectoryWalker($path);

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

	public static function getSrcDirectory():string {
		// TODO.
	}

	public static function getWwwDirectory():string {
		// TODO.
	}

	public static function getPageDirectory():string {
		// TODO.
	}

	public static function getApiDirectory():string {
		// TODO.
	}

	public static function getDataDirectory():string {
		// TODO.
	}

	public static function getAssetDirectory():string {
		// TODO.
	}

	public static function getScriptDirectory():string {
		// TODO.
	}

	public static function getStyleDirectory():string {
		// TODO.
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

			if(!file_exists($output)) {
				$test = "test";
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