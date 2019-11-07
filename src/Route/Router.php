<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Assembly;
use Gt\WebEngine\FileSystem\Path;
use Psr\Http\Message\RequestInterface;

abstract class Router {
	const DEFAULT_BASENAME = "index";
	const LOGIC_EXTENSIONS = ["php"];
	const VIEW_EXTENSIONS = ["html"];
	const LOGIC_BEFORE = ["_before", "_common"];
	const LOGIC_AFTER = ["_after"];
	const VIEW_BEFORE = ["_header"];
	const VIEW_AFTER = ["_footer"];

	/** @var RequestInterface */
	protected $request;
	/** @var string */
	protected $documentRoot;

	public function __construct(RequestInterface $request, string $documentRoot) {
		$this->request = $request;
		$this->documentRoot = $documentRoot;
	}

	/**
	 * The base view-logic path is the absolute path on disk to where all View and Logic files
	 * exist, according to the current Route type.
	 */
	abstract public function getBaseViewLogicPath():string;

	public function redirectIndex(string $uri):void {
//		$directory = $this->getDirectoryForUri($uri);
		$basename = $this->getBasenameForUri($uri);
		$lastSlashPosition = strrpos(
			$uri,
			DIRECTORY_SEPARATOR
		);
		$partAfterSlash = substr(
			$uri,
			$lastSlashPosition + 1
		);

		if($basename === self::DEFAULT_BASENAME
		&& $partAfterSlash === self::DEFAULT_BASENAME) {
			$newUri = substr($uri, 0, strrpos($uri, "/"));
			header(
				"Location: $newUri",
				true,
				303
			);
		}
	}

	public function getViewAssembly(string $uri):Assembly {
		$directory = $this->getDirectoryForUri($uri);
		$basename = $this->getBasenameForUri($uri);

		$assembly = new Assembly(
			$this->getBaseViewLogicPath(),
			$directory,
			$basename,
			static::VIEW_EXTENSIONS,
			static::VIEW_BEFORE,
			static::VIEW_AFTER,
			true
		);

		return $assembly;
	}

	public function getLogicAssembly(string $uri):Assembly {
		$directory = $this->getDirectoryForUri($uri);
		$basename = $this->getBasenameForUri($uri);

		$assembly = new Assembly(
			$this->getBaseViewLogicPath(),
			$directory,
			$basename,
			static::LOGIC_EXTENSIONS,
			static::LOGIC_BEFORE,
			static::LOGIC_AFTER
		);
		return $assembly;
	}

	protected function getDirectoryForUri(string $uri):string {
		$basePath = $this->getBaseViewLogicPath();
		$subPath = $this->getViewLogicSubPath($uri);
		$absolutePath = $basePath . $subPath;

		if(Path::isDynamic($absolutePath)) {
			$lastSlashPosition = strrpos(
				$subPath,
				DIRECTORY_SEPARATOR
			);
			$subPath = substr(
				$subPath,
				0,
				$lastSlashPosition
			);
		}

// Note: use of forward slash here is correct due to working with URL, not directory path.
		$subPath = str_replace(
			"/",
			DIRECTORY_SEPARATOR,
			$subPath
		);
		return $subPath;
	}

	protected function getBasenameForUri(string $uri):string {
		$pageDirPath = $this->getBaseViewLogicPath();
		$subDirPath = $this->getViewLogicSubPath($uri);
		$fileBasename = $this->getViewLogicBasename($uri);

		$absolutePath = $pageDirPath . $subDirPath . "/" . $fileBasename;
		$lastSlashPosition = strrpos(
			$subDirPath,
			DIRECTORY_SEPARATOR
		);

		if(Path::isDynamic($absolutePath)) {
			$fileBasename = substr(
				$absolutePath,
				$lastSlashPosition + 1
			);
		}

		return $fileBasename;
	}

	/**
	 * The view-logic sub-path is the path on disk to the directory containing the requested
	 * View and Logic files, relative to the base view-logic path.
	 */
	protected function getViewLogicSubPath(string $uriPath):string {
		$uriPath = str_replace(
			"/",
			DIRECTORY_SEPARATOR,
			$uriPath
		);
		$baseViewLogicPath = $this->getBaseViewLogicPath();
		$absolutePath = $baseViewLogicPath . $uriPath;

		if(!is_dir($absolutePath)) {
			$absolutePath = dirname($absolutePath);
		}

		$relativePath = substr($absolutePath, strlen($baseViewLogicPath));
		if(strlen($relativePath) >= 1) {
			$relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
		}

		return $relativePath;
	}

	protected function getViewLogicBasename(string $uriPath):string {
		$basename = self::DEFAULT_BASENAME;

		$uriPath = str_replace(
			"/",
			DIRECTORY_SEPARATOR,
			$uriPath
		);

//		if(substr($uriPath, -1) === DIRECTORY_SEPARATOR) {
//			$uriPath .= $basename;
//		}

		$baseViewLogicPath = $this->getBaseViewLogicPath();
		$absolutePath = $baseViewLogicPath . $uriPath;

		$lastSlashPos = strrpos($uriPath, "/");
		$lastSlashAbsolutePos = strrpos($absolutePath, "/");
		$lastPathPart = substr($uriPath, $lastSlashPos + 1);
		$absolutePathWithoutLastPart = substr(
			$absolutePath,
			0,
			$lastSlashAbsolutePos
		);

		$matchingPageFiles = glob("$absolutePath.*");
		$matchingDynamics = glob("$absolutePathWithoutLastPart/@*");
		if(!empty($matchingPageFiles)
		|| !empty($matchingDynamics)) {
			$basename = $lastPathPart ?: self::DEFAULT_BASENAME;
		}

		return $basename;
	}
}