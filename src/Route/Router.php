<?php
namespace Gt\WebEngine\Route;

use DirectoryIterator;
use Gt\WebEngine\FileSystem\Assembly;
use Psr\Http\Message\RequestInterface;

abstract class Router {
	const DEFAULT_BASENAME = "index";
	const LOGIC_EXTENSIONS = ["php"];
	const VIEW_EXTENSIONS = [];
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

	public function getLogicAssembly(string $uri):Assembly {
		$directory = $this->getDirectoryForUri($uri);
		$basename = $this->getBasenameForUri($uri);
		$logicOrder = array_merge(
			static::LOGIC_BEFORE,
			[$basename],
			static::LOGIC_AFTER
		);
		$assembly = new Assembly(
			$this->getBaseViewLogicPath(),
			$directory,
			self::LOGIC_EXTENSIONS,
			$logicOrder
		);
		var_dump($assembly);die();
	}

	protected function getDirectoryForUri(string $uri):string {
		$basePath = $this->getBaseViewLogicPath();
		$subPath = $this->getViewLogicSubPath($uri);

		if(!is_dir($basePath . $subPath)) {
			$lastSlashPosition = strrpos($subPath, "/");
			$subPath = substr(
				$subPath,
				0,
				$lastSlashPosition
			);
		}

		return $subPath;
	}

	protected function getBasenameForUri(string $uri):string {
		$basePath = $this->getBaseViewLogicPath();
		$subPath = $this->getViewLogicSubPath($uri);
		$baseName = static::DEFAULT_BASENAME;

		if(!is_dir($basePath . $subPath)) {
			$lastSlashPosition = strrpos($subPath, "/");
			$baseName = substr(
				$subPath,
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
		$baseViewLogicPath = $this->getBaseViewLogicPath();
		$absolutePath = $baseViewLogicPath . $uriPath;

		$relativePath = substr($absolutePath, strlen($baseViewLogicPath));
		if(strlen($relativePath) > 1) {
			$relativePath = rtrim($relativePath, "/");
		}
		return $relativePath;
	}
}