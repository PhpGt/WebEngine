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
	 * exist, according to the current Router type.
	 */
	abstract public function getBaseViewLogicPath():string;

	public function redirectIndex(string $uri):void {
		$directory = $this->getDirectoryForUri($uri);
		$basename = $this->getBasenameForUri($uri);
		$lastSlashPosition = strrpos(
			$directory,
			DIRECTORY_SEPARATOR
		);
		$directoryAfterSlash = substr(
			$directory,
			$lastSlashPosition + 1
		);

		if($basename === self::DEFAULT_BASENAME
		&& $directoryAfterSlash === self::DEFAULT_BASENAME) {
			$uri = substr($uri, 0, strrpos($uri, "/"));
			if(strlen($uri) === 0) {
				$uri = "/";
			}

			header(
				"Location: $uri",
				true,
				303
			);
			exit;
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
		$basePath = $this->getBaseViewLogicPath();
		$subPath = $this->getViewLogicSubPath($uri);
		$baseName = static::DEFAULT_BASENAME;

		$absolutePath = $basePath . $subPath;
		$lastSlashPosition = strrpos(
			$subPath,
			DIRECTORY_SEPARATOR
		);

		if(Path::isDynamic($absolutePath)) {
			$baseName = substr(
				$absolutePath,
				$lastSlashPosition + 1
			);
		}


		return $baseName;
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

		$relativePath = substr($absolutePath, strlen($baseViewLogicPath));
		if(strlen($relativePath) > 1) {
			$relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
		}
		return $relativePath;
	}
}