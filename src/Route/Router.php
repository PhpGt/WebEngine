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

	abstract public function getViewLogicPath():string;
}