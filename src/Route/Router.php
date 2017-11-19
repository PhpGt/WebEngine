<?php
namespace Gt\WebEngine\Route;

use DirectoryIterator;
use Psr\Http\Message\RequestInterface;

abstract class Router {
	const DEFAULT_BASENAME = "index";

	/** @var RequestInterface */
	protected $request;

	public function __construct(RequestInterface $request) {
		$this->request = $request;
	}

	/**
	 * The base view-logic path is the absolute path on disk to where all View and Logic files
	 * exist, according to the current Router type.
	 */
	abstract public function getBaseViewLogicPath():string;

	public function getViewFile(string $uriPath):?string {
		$baseViewLogicPath = $this->getBaseViewLogicPath();
		$viewFileSubPath = $this->getViewLogicSubPath($uriPath);
		$viewFileBaseName = self::DEFAULT_BASENAME;

		if(!is_dir($baseViewLogicPath . $viewFileSubPath)) {
			$lastSlashPosition = strrpos($viewFileSubPath, "/");
			$viewFileBaseName = substr(
				$viewFileSubPath,
				$lastSlashPosition + 1
			);
			$viewFileSubPath = substr(
				$viewFileSubPath,
				0,
				$lastSlashPosition
			);
		}

		foreach(new DirectoryIterator($baseViewLogicPath . $viewFileSubPath) as $fileInfo) {
			if($fileInfo->isDir()) {
				continue;
			}

			$extension = $fileInfo->getExtension();
			$baseName = $fileInfo->getBasename("." . $extension);

			if($baseName !== $viewFileBaseName) {
				continue;
			}

			return $fileInfo->getRealPath();
		}

		return null;
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