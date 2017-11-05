<?php
namespace Gt\WebEngine;

use Gt\Http\RequestFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;

class Lifecycle {
	protected static $input;
	protected static $request;
	protected static $response;

	public static function start() {
		self::createWrapperObjects();
		self::createRequestResponse();
		self::dispatch();
		self::finish();
	}

	public static function createWrapperObjects() {
		self::$input = new Input();
//		self::$cookie = new Cookie();
//		self::$session = new Session();
//		self::$env = new Env();
	}

	public static function createRequestResponse() {
		self::$request = RequestFactory::create();
		// self::$response = ResponseFactory::create($request);
	}

	public static function dispatch() {
		$dispatcher = DispatcherFactory::create(
			self::$request//,
//			self::$input
		);
		$dispatcher->process();
	}

	public static function finish() {

	}
}