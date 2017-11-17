<?php
namespace Gt\WebEngine\Route;

use Gt\Http\Uri;
use Psr\Http\Message\RequestInterface;

abstract class Router {
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

	/**
	 * The view-logic sub-path is the path on disk to the directory containing the requested
	 * View and Logic files, relative to the base view-logic path.
	 */
	public function getViewLogicSubPath(string $path):string {
		var_dump($path);die("ITS THE PATH");
	}
}