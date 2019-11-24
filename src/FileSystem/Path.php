<?php
namespace Gt\WebEngine\FileSystem;

use DirectoryIterator;
use Gt\WebEngine\Route\Router;

class Path {
	protected static $appRoot;

	public static function getApplicationRootDirectory(string $innerDirectory):string {
		$directoryWalker = new DirectoryWalker($innerDirectory);

		return self::fixPath(
			$directoryWalker->findParentContaining("composer.json")
		);
	}

	public static function getGtRootDirectory():string {
		$directoryWalker = new DirectoryWalker(__DIR__);
		return self::fixPath(
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

	public static function fixPath(
		string $path
	):string {
		if(file_exists($path)) {
			return $path;
		}

// TODO: This breaks within a "jailed" Linux user. See https://github.com/PhpGt/WebEngine/issues/260
// Use a base directory of "getApplicationRootDirectory", and have this check for a constant
// defined in go.php?
		$fixed = "";
		if(DIRECTORY_SEPARATOR === "/") {
			$fixed .= DIRECTORY_SEPARATOR;
		}
		$path = str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $path);
		$pathParts = explode(DIRECTORY_SEPARATOR, $path);

		foreach($pathParts as $directory) {
			$currentSearchPath = $fixed;
			$currentSearchPath .= $directory;

// If the directory exists without its path being changed, use that and continue to next child.
			if(is_dir($currentSearchPath)) {
				$fixed = "$currentSearchPath";

				if(strlen($fixed) > 1) {
					$fixed .= DIRECTORY_SEPARATOR;
				}
				continue;
			}

			$iterator = new DirectoryIterator($fixed);
			$foundMatch = false;
			foreach($iterator as $fileInfo) {
				$fileName = $fileInfo->getFilename();
				if($fileName === "." || $fileName === "..") {
					continue;
				}

				if(strtolower($fileName) === strtolower($directory)) {
					$fixed .= $fileName . DIRECTORY_SEPARATOR;
					$foundMatch = true;
					break;
				}

				$directoryWithHyphensParts = preg_split(
					'/(?=[A-Z])/',
					$directory
				);
				$directoryWithHyphensParts = array_filter($directoryWithHyphensParts);

				$directoryWithHyphens = implode(
					"-",
					$directoryWithHyphensParts
				);

				$directoryWithHyphens = str_replace(
					"_-",
					"@",
					$directoryWithHyphens
				);

				if(strtolower($fileName) === strtolower($directoryWithHyphens)) {
					$fixed .= $fileName . DIRECTORY_SEPARATOR;
					$foundMatch = true;
					break;
				}
			}

			if(!$foundMatch) {
				throw new PathNotFound($path);
			}
		}

		$fixed = rtrim($fixed, DIRECTORY_SEPARATOR);
		return $fixed;
	}

	public static function isDynamic(string $absolutePath):bool {
		$pathParts = explode(
			DIRECTORY_SEPARATOR,
			$absolutePath
		);
		while(count($pathParts) > 2) {
			$removed = array_pop($pathParts);
			$searchPath = implode(
				DIRECTORY_SEPARATOR,
				$pathParts
			);
			if($removed === Router::DEFAULT_BASENAME) {
				$indexFiles = glob("$searchPath/index.*");
				if(!empty($indexFiles)) {
					return false;
				}
			}
			$dynamicFiles = glob("$searchPath/@*");
			if(!empty($dynamicFiles)) {
				return true;
			}
		}
		return false;
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