<?php
namespace Gt\FileSystem;

class Path {
	public static function getApplicationRoot(string $document_root = null):string {
		if(is_null($document_root)) {
			$document_root = $_SERVER["DOCUMENT_ROOT"];
		}

		$path = getcwd();

		if(!empty($document_root)) {
			$path = dirname($document_root);
		}

		$directory_walker = new DirectoryWalker($path);
		return $directory_walker->findParentContaining("src");
	}
}