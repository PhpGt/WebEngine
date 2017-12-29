<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\Router;

class DispatcherFactory {
	public static function create(Router $router, string $appNamespace):Dispatcher {
		if($router instanceof PageRouter) {
			return new PageDispatcher($router, $appNamespace);
		}
		if($router instanceof ApiRouter) {
			return new ApiDispatcher($router, $appNamespace);
		}
	}
}