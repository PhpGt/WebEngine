<?php
namespace Gt\FileSystem;

class Path {
	const ROOT			= "root";
	const GTROOT			= "gtroot";
	const SRC			= "src";
	const WWW			= "www";
	const PAGE			= "page";
	const API			= "api";
	const DATA			= "data";
	const ASSET			= "asset";
	const SCRIPT			= "script";
	const STYLE			= "style";

	public static function getApplicationRoot(string $documentRoot = null):string {
		if(is_null($documentRoot)) {
			$documentRoot = $_SERVER["DOCUMENT_ROOT"];
		}

		$path = getcwd();

		if(!empty($documentRoot)) {
			$path = dirname($documentRoot);
		}

		$directoryWalker = new DirectoryWalker($path);
		return $directoryWalker->findParentContaining("src");
	}
}