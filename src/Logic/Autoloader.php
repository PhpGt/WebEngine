<?php
namespace Gt\WebEngine\Logic;
use DirectoryIterator;
use Gt\WebEngine\FileSystem\Path;

/**
 * Logic files use their own autoloader to allow loading classes that are web-mapped.
 * @see https://github.com/PhpGt/StyleGuide/blob/master/directories-files-namespaces/path-mapping.md#web-mapping-example
 */
class Autoloader {
	const ALLOWED_SUFFIXES = [
		"Api",
		"Page",
	];

	protected $appNamespace;
	protected $docRoot;

	public function __construct(string $appNamespace, string $docRoot) {
		$this->appNamespace = $appNamespace;
		$this->docRoot = $docRoot;
	}

	/**
	 * @return ?string Returns the absolute classname of the autoloaded
	 * class, or null if the required class isn't a Logic class.
	 * @throws \Gt\WebEngine\FileSystem\PathNotFound
	 */
	public function autoload(string $absoluteClassName):?string {
		$classSuffix = $this->getClassSuffix($absoluteClassName);
		if(is_null($classSuffix)) {
			return null;
		}

		$absoluteClassName = trim($absoluteClassName, "\\");
		if(strpos(
			$absoluteClassName,
			$this->appNamespace
		) !== 0) {
			return null;
		}

		$logicType = substr(
			$absoluteClassName,
			strlen($this->appNamespace) + 1
		);
		$logicType = substr(
			$logicType,
			0,
			strpos($logicType, "\\")
		);

		$path = $this->getPathForLogicType($logicType);
		$toRemove = explode("\\", $this->appNamespace);
		$toRemove []= $logicType;

		$relativeClassName = $this->getRelativeClassName(
			$absoluteClassName,
			...$toRemove
		);

		$directoryPath = $this->buildDirectoryPathFromRelativeClassName(
			$path,
			$relativeClassName
		);

		$fileName = $this->findFileName(
			$directoryPath,
			$relativeClassName,
			$classSuffix
		);

		$autoloadPath = implode(DIRECTORY_SEPARATOR, [
			$directoryPath,
			$fileName,
		]);

		$autoloadPath = Path::fixPath($autoloadPath);
		return $this->requireAndCheck($autoloadPath, $absoluteClassName);
	}

	protected function requireAndCheck(string $filePath, string $className):string {
		if(!is_file($filePath)) {
			throw new AutoloaderException("File path is not correct for when autoloading class '$className'");
		}
		require($filePath);

		if($className[0] !== "\\") {
			$className = "\\" . $className;
		}
		return $className;
	}

	protected function getClassSuffix($className):?string {
		$classSuffix = null;

		foreach(self::ALLOWED_SUFFIXES as $suffix) {
			if($this->classHasSuffix($className, $suffix)) {
				$classSuffix = $suffix;
			}
		}

		return $classSuffix ;
	}

	protected function classHasSuffix($className, $endsWith):bool {
		$length = strlen($endsWith);

		return $length === 0
		|| (substr($className, -$length) === $endsWith);
	}

	protected function getPathForLogicType(string $type):string {
		switch(strtolower($type)) {
		case "api":
			$path = Path::getApiDirectory($this->docRoot);
			break;

		case "page":
			$path = Path::getPageDirectory($this->docRoot);
			break;
		}

		return $path;
	}

	protected function getRelativeClassName(string $absoluteClassName, string...$toRemove) {
		$parts = explode("\\", $absoluteClassName);
		foreach($toRemove as $remove) {
			if($remove === $parts[0]) {
				array_shift($parts);
			}
		}

		return implode("\\", $parts);
	}

	protected function buildDirectoryPathFromRelativeClassName(
		string $path,
		string $relativeClassName
	):string {
		$parts = explode("\\", $relativeClassName);
		$partsToRemove = explode("\\", $this->appNamespace);
		array_pop($parts);

		foreach($parts as $part) {
			$path .= DIRECTORY_SEPARATOR . $part;
		}

		if(!is_dir($path)) {
// The path of the file on-disk may not always match the class name, due to
// web-mapping vs. namespace mapping
// @see https://github.com/PhpGt/StyleGuide/blob/master/directories-files-namespaces/path-mapping.md
			$path = Path::fixPath($path);
		}

		return $path;
	}

	protected function findFileName(
		string $directoryPath,
		string $relativeClassName,
		string $classSuffix
	):string {
		$matchingFileName = null;

		$parts = explode("\\", $relativeClassName);
		$className = array_pop($parts);
		$suffixPosition = strrpos(
			$className,
			$classSuffix
		);
		$searchFileName = substr($className,0, $suffixPosition);
		$searchFileName = "$searchFileName.php";

		$subDirectoryPath = $directoryPath;

		$subDirectoryPath = str_replace(
			DIRECTORY_SEPARATOR . "_",
			DIRECTORY_SEPARATOR . "@",
			$subDirectoryPath
		);

		$subDirectoryPath = Path::fixPath($subDirectoryPath);

		$searchFileNameLowerCase = strtolower($searchFileName);
		$searchFileNameHyphenatedLowerCase = strtolower(
			$this->hyphenate($searchFileName)
		);
		$searchList = [
			$searchFileNameLowerCase,
			$searchFileNameHyphenatedLowerCase,
			str_replace("_", "@", $searchFileNameLowerCase),
			str_replace("_", "@", $searchFileNameHyphenatedLowerCase),
		];

		foreach(new DirectoryIterator($subDirectoryPath) as $fileInfo) {
			if(!$fileInfo->isFile()) {
				continue;
			}

			$fileName = $fileInfo->getFilename();
			$fileNameLowerCase = strtolower($fileName);
			if(!in_array($fileNameLowerCase, $searchList)) {
				continue;
			}

			$matchingFileName = $fileName;
		}

		$relativeFileName = substr($subDirectoryPath, strlen($directoryPath));
		$relativeFileName = trim($relativeFileName, "\\/");
		$relativeFileName = implode(DIRECTORY_SEPARATOR, [
			$relativeFileName,
			$matchingFileName,
		]);

		return $relativeFileName;
	}

	protected function hyphenate(string $fileName):string {
		$file = pathinfo(
			$fileName,
			PATHINFO_FILENAME
		);
		$extension = pathinfo(
			$fileName,
			PATHINFO_EXTENSION
		);

		for($i = strlen($file) - 1; $i > 0; $i--) {
			if(!ctype_upper($file[$i])) {
				continue;
			}

			$fileName = substr_replace(
				$fileName,
				"-",
				$i,
				0
			);
		}

		return $fileName;
	}
}