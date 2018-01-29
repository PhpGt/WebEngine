<?php
namespace Gt\WebEngine\Route;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterFactory {
	protected static $routerClassLookup = [];

	public static function create(
		RequestInterface $request,
		string $documentRoot
	):Router {
// TODO: Where should we decide on the type of router?
		$router = new PageRouter(
			$request,
			$documentRoot
		);
		return $router;
	}
}