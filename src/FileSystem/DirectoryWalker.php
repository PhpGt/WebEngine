<?php
namespace Gt\FileSystem;

class DirectoryWalker {
	/** @var string */
	protected $directory;

	public function __construct(string $directory) {
		$this->directory = $directory;
	}

	public function findParentContaining(string $match):?string {
		$path = $this->directory;

		while($path !== "/"
		&& !in_array($match, scandir($path))) {
			$path = realpath("$path/..");
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
}