<?php
namespace Gt\WebEngine\FileSystem;

use Iterator;

class Assembly implements Iterator {
	protected $path;
	protected $extensions;
	protected $basename;
	protected $lookupBefore;
	protected $lookupAfter;

	protected $assemblyParts;
	protected $iteratorKey;

	/**
	 * @throws BasenameNotFoundException
	 */
	public function __construct(
		string $basePath,
		string $directory,
		string $basename,
		array $extensions,
		array $lookupBefore,
		array $lookupAfter,
		bool $basenameMustExist = false
	) {
		$this->path = realpath($basePath . $directory);
		$this->extensions = $extensions;
		$this->basename = $basename;
		$this->lookupBefore = $lookupBefore;
		$this->lookupAfter = $lookupAfter;

		if($basenameMustExist) {
			$basenamePath = $this->findInDirectory($basename);
			if(is_null($basenamePath)) {
				throw new BasenameNotFoundException($basename);
			}
		}

		$this->assemblyParts = $this->getAssemblyParts();
	}

	public function __toString():string {
		$string = "";

		foreach($this->assemblyParts as $part) {
			$string .= file_get_contents($part);
		}

		return $string;
	}

	protected function getAssemblyParts():array {
		$parts = [];

		foreach($this->lookupBefore as $lookup) {
			$parts []= $this->findInDirectory($lookup, true);
		}

		$parts []=  $this->findInDirectory($this->basename, false);

		foreach($this->lookupAfter as $lookup) {
			$parts []= $this->findInDirectory($lookup, true);
		}

		return array_values(array_filter($parts));
	}

	protected function findInDirectory(string $basename, bool $bubbleUp = false):?string {
		$foundPath = null;

		$appRoot = Path::getApplicationRootDirectory($this->path);
		$highestPath = Path::getSrcDirectory($appRoot);

		$path = $this->path;
		do {
			$extensionString = implode(",", $this->extensions);
			$extensionGlob = implode("", [
				"{",
				$extensionString,
				"}",
			]);

			$glob = implode(DIRECTORY_SEPARATOR, [
				$path,
				"$basename.$extensionGlob",
			]);
			$matches = glob($glob, GLOB_BRACE );

			if(!empty($matches)) {
				$foundPath = $matches[0];
			}

			$path = dirname($path);
		} while($bubbleUp && $path !== $highestPath);

		return $foundPath;
	}

	public function current():string {
		return $this->assemblyParts[$this->iteratorKey];
	}

	public function next():void {
		$this->iteratorKey ++;
	}

	public function key():int {
		return $this->iteratorKey;
	}

	public function valid():bool {
		return isset($this->assemblyParts[$this->iteratorKey]);
	}

	public function rewind():void {
		$this->iteratorKey = 0;
	}
}