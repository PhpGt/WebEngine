<?php
namespace Gt\WebEngine\FileSystem;

use Iterator;
use SplFileObject;

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
		$this->path = $this->getPath($basePath, $directory);
		$this->extensions = $extensions;
		$this->basename = $basename;
		$this->lookupBefore = $lookupBefore;
		$this->lookupAfter = $lookupAfter;
		$before = true;
		$after = true;

		if($basenameMustExist) {
			$basenamePath = $this->findInDirectory($basename);

			if(is_null($basenamePath)) {
				throw new BasenameNotFoundException($basename);
			}

			$basenameFile = new SplFileObject(
				$basenamePath,
				"r"
			);
			$line = $basenameFile->getCurrentLine();
			$basenameFile = null;

			if(strstr($line, "no-")) {
				if(strstr($line, "no-header")) {
					$before = false;
				}
				if(strstr($line, "no-footer")) {
					$after = false;
				}
				if(strstr(
					$line,
					"no-header-footer")
				) {
					$before = false;
					$after = false;
				}
			}
		}

		$this->assemblyParts = $this->getAssemblyParts(
			$before,
			$after
		);
	}

	public function __toString():string {
		$string = "";

		foreach($this->assemblyParts as $part) {
			$string .= file_get_contents($part);
		}

		return $string;
	}

	protected function getAssemblyParts(
		bool $before = true,
		bool $after = true
	):array {
		$parts = [];

		if($before) {
			foreach($this->lookupBefore as $lookup) {
				$parts []= $this->findInDirectory(
					$lookup,
					true
				);
			}
		}

		$parts []= $this->findInDirectory(
			$this->basename,
			false
		);

		if($after) {
			foreach($this->lookupAfter as $lookup) {
				$parts []= $this->findInDirectory(
					$lookup,
					true
				);
			}
		}


		$parts = array_filter($parts);
		$parts = array_unique($parts);
		return array_values(array_filter($parts));
	}

	protected function findInDirectory(string $basename, bool $bubbleUp = false):?string {
		$foundPath = null;
		$appRoot = Path::getApplicationRootDirectory($this->path);

		$path = $this->path;
		do {
			$extensionString = implode(",", $this->extensions);
			$extensionGlob = implode("", [
				"{",
				$extensionString,
				"}",
			]);

			$baseNamesToMatch = [
				$basename,
			];

			if($basename[0] !== "_") {
				$baseNamesToMatch []= "@*";
			}

			foreach($baseNamesToMatch as $baseNameToMatch) {
				$glob = implode(DIRECTORY_SEPARATOR, [
					$path,
					"$baseNameToMatch.$extensionGlob",
				]);
				$matches = glob($glob, GLOB_BRACE);

				if(!empty($matches)) {
					$foundPath = $matches[0];
					break;
				}
			}

			$path = dirname($path);
		} while($bubbleUp && $path !== $appRoot);

		return $foundPath;
	}

	private function getPath(string $baseName, string $directory):?string {
		$path = realpath($baseName . $directory);

// If the path exists, simply return it.
		if($path !== false) {
			return $path;
		}

// Replace the path with any magic directories that exist.
		$pathToScan = $baseName;
		$subDirectoryParts = explode(
			DIRECTORY_SEPARATOR,
			$directory
		);
		$subDirectoryParts = array_filter($subDirectoryParts);

		do {
			$fileList = scandir($pathToScan);
			$nextDirName = array_shift($subDirectoryParts);
			if(in_array($nextDirName, $fileList)) {
				$pathToScan .= DIRECTORY_SEPARATOR . $nextDirName;
			}
			else {
				$magicDirectory = null;

				foreach($fileList as $file) {
					if($file[0] !== "@") {
						continue;
					}

					$magicDirectory = $file;
				}

				if(is_null($magicDirectory)) {
					break;
				}

				$pathToScan .= DIRECTORY_SEPARATOR . $magicDirectory;
			}
		} while(!empty($subDirectoryParts));

		if(is_dir($pathToScan)) {
			return $pathToScan;
		}

		return null;
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