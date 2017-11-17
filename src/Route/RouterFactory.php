<?php
namespace Gt\WebEngine\Route;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterFactory {
	protected static $routerClassLookup = [];

	public static function registerRouterClassForResponse(
		string $routerClassName,
		string $responseClassName
	):void {
		static::$routerClassLookup[$responseClassName] = $routerClassName;
	}

	public static function create(
		RequestInterface $request,
		ResponseInterface $response
	):Router {
		$router = new static::$routerClassLookup[get_class($response)]($request);
		return $router;
	}
}