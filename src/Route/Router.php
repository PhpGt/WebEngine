<?php
namespace Gt\WebEngine\Route;

use Gt\WebEngine\FileSystem\Assembly;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

abstract class Router {
	const DEFAULT_BASENAME = "index";
	const LOGIC_EXTENSIONS = ["php"];
	const VIEW_EXTENSIONS = ["html"];
	const LOGIC_BEFORE = ["_setup", "_before", "_common"];
	const LOGIC_AFTER = ["_after"];
	const VIEW_BEFORE = ["_header"];
	const VIEW_AFTER = ["_footer"];

	/** @var RequestInterface */
	protected $request;
	/** @var string */
	protected $documentRoot;
	/** @var string */
	protected $contentType;
	/** @var string */
	protected $baseViewLogicPath;
	/** @var string */
	protected $viewLogicPath;
	/** @var string */
	protected $viewLogicBasename;

	public function __construct(
		RequestInterface $request,
		string $documentRoot,
		string $contentType
	) {
		$this->request = $request;
		$this->documentRoot = $documentRoot;
		$this->contentType = $contentType;

		$uri = $request->getUri();
		$this->baseViewLogicPath = $this->getBaseViewLogicPath();
		$this->viewLogicPath = $this->getViewLogicPath($uri);
		$this->viewLogicBasename = $this->getViewLogicBasename($uri);
	}

	/**
	 * The base view-logic path is the absolute path on disk to where all View and Logic files
	 * exist, according to the current Route type.
	 */
	abstract public function getBaseViewLogicPath():string;

	public function redirectInvalidPaths(string $uri):void {
		if(strlen($uri) > 1
		&& substr($uri, -1) === "/") {
			header(
				"Location: " . rtrim($uri, "/"),
				true,
				303
			);
			return;
		}

		if($this->viewLogicBasename !== self::DEFAULT_BASENAME) {
			return;
		}

		$lastSlashPosition = strrpos($uri, "/");
		$lastPieceOfUri = substr($uri, $lastSlashPosition + 1);

		if($lastPieceOfUri !== self::DEFAULT_BASENAME) {
			return;
		}

		$uri = substr($uri, 0, $lastSlashPosition);
		if(strlen($uri) === 0) {
			$uri = "/";
		}

		header(
			"Location: $uri",
			true,
			303
		);
	}

	public function getViewAssembly():Assembly {
		return new Assembly(
			$this->baseViewLogicPath,
			$this->viewLogicPath,
			$this->viewLogicBasename,
			static::VIEW_EXTENSIONS,
			static::VIEW_BEFORE,
			static::VIEW_AFTER,
			true
		);
	}

	public function getLogicAssembly():Assembly {
		return new Assembly(
			$this->baseViewLogicPath,
			$this->viewLogicPath,
			$this->viewLogicBasename,
			static::LOGIC_EXTENSIONS,
			static::LOGIC_BEFORE,
			static::LOGIC_AFTER
		);
	}

	public function getContentType():string {
		return $this->contentType;
	}

	protected function getViewLogicBasename(UriInterface $uri):?string {
		$uriPath = $uri->getPath();
		$basename = self::DEFAULT_BASENAME;

		if($uriPath === "/") {
			return $basename;
		}

		$absolutePath = $this->baseViewLogicPath
			. str_replace(
				"/",
				DIRECTORY_SEPARATOR,
				$uriPath
			);

		if($this->isAddressableFile($absolutePath)
		|| !$this->isAddressableDir($absolutePath)) {
			$basename = pathinfo(
				$absolutePath,
				PATHINFO_BASENAME
			);
		}

		return $basename;
	}

	protected function isAddressableFile(string $absolutePath):bool {
		$matches = glob("$absolutePath.*");
		if(!empty($matches) && is_file($matches[0])) {
			return true;
		}

		return $this->isDynamicFile($absolutePath);
	}

	protected function isAddressableDir(string $absolutePath):bool {
		if(is_dir($absolutePath)) {
			return true;
		}

		return $this->isDynamicDir($absolutePath);
	}

	protected function isDynamicFile(string $absolutePath):bool {
		if(file_exists($absolutePath)) {
			return false;
		}

// TODO: Why are we only returning false here?
		return false;
	}

	protected function isDynamicDir(string $absolutePath):bool {
		if(file_exists($absolutePath)) {
			return false;
		}

// TODO: Why are we only returning false here?
		return false;
	}

	/**
	 * The view-logic sub-path is the path on disk to the directory
	 * containing the requested View and Logic files,
	 * relative to the base view-logic path.
	 */
	protected function getViewLogicPath(UriInterface $uri):string {
		$uriPath = $uri->getPath();
		$uriPath = str_replace(
			"/",
			DIRECTORY_SEPARATOR,
			$uriPath
		);
		$absolutePath = $this->baseViewLogicPath . $uriPath;

		if(!is_dir($absolutePath)) {
			$dynamicMatches = glob("$absolutePath/@*");
			foreach($dynamicMatches as $match) {
				if(is_dir($match)) {
					$absolutePath = $match;
					break;
				}
			}
		}

		return substr($absolutePath, strlen($this->baseViewLogicPath));
	}
}