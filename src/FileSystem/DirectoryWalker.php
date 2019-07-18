<?php
namespace Gt\WebEngine\FileSystem;

class DirectoryWalker {
	/** @var string */
	protected $directory;

	public function __construct(string $directory) {
		$this->directory = $directory;
	}

	public function findParentContaining(string $match):?string {
		$path = $this->directory;

		while(strlen($path) > 0
		&& (!$this->pathIsRoot($path)
		&& !$this->directoryContains($path, $match))) {
			$lastSlashPos = strrpos($path, DIRECTORY_SEPARATOR);
			$path = substr($path, 0, $lastSlashPos);
		}

		if($path === "/") {
			if($match === "/"
			|| empty($match)) {
				return $path;
			}
			else {
				return null;
			}
		}

		return $path;
	}

	private function pathIsRoot(string $path):bool {
// Unix root path is a simple slash.
		if(DIRECTORY_SEPARATOR === "/") {
			return $path === "/";
		}

// Windows root path is X:\
		return (
			substr_count($path, DIRECTORY_SEPARATOR) === 1
			&& $path[1] === ":"
		);
	}

	private function directoryContains(string $directory, string $match):bool {
		return file_exists(implode(DIRECTORY_SEPARATOR, [
			$directory,
			$match,
		]));
	}
}