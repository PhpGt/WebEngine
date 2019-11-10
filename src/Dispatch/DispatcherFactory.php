<?php
namespace Gt\WebEngine\Dispatch;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Cookie\CookieHandler;
use Gt\Csrf\TokenStore;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\Router;

class DispatcherFactory {
	public static function create(
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookie,
		Session $session,
		Database $database,
		Router $router,
		TokenStore $csrfProtection
	):Dispatcher {
		$appNamespace = $config->get("app.namespace");
		$dispatcher = null;

		if($router instanceof PageRouter) {
			$dispatcher = new PageDispatcher($router, $appNamespace);
		}
		if($router instanceof ApiRouter) {
			$dispatcher = new ApiDispatcher($router, $appNamespace);
		}

		$dispatcher->storeInternalObjects(
			$config,
			$serverInfo,
			$input,
			$cookie,
			$session,
			$database
		);

		$dispatcher->setCsrfProtection($csrfProtection);

		return $dispatcher;
	}
}