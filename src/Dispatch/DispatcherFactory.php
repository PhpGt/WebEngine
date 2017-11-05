<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\Route\Router;

class DispatcherFactory {
	public static function create(Router $router):Dispatcher {
		$dispatcher = new Dispatcher();
		return $dispatcher;
	}
}