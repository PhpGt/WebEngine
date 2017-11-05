<?php
namespace Gt\WebEngine\Dispatch;

use Gt\Http\Request;

class DispatcherFactory {
	public static function create(Request $request):Dispatcher {
		$dispatcher = new Dispatcher();
	}
}