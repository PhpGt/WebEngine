<?php
namespace Gt\WebEngine;

use Psr\Http\Message\RequestInterface;

class RouterFactory {
	public static function create(RequestInterface $request):Router {
		$router = new Router($request);
	}
}