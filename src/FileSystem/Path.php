<?php
namespace Gt\FileSystem;

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
		// TODO.
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
		return self::fixPathCase(implode(DIRECTORY_SEPARATOR, [
			self::getSrcDirectory(),
			$name,
		]));
	}

	public static function fixPathCase(string $path):string {
		// TODO.
	}
}