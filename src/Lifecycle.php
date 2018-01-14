<?php
namespace Gt\WebEngine;

use Gt\Config\Config;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Cookie\Cookie;
use Gt\ProtectedGlobal\Protection;
use Gt\Session\Session;
use Gt\Http\RequestFactory;
use Gt\Http\ResponseFactory;
use Gt\WebEngine\Dispatch\Dispatcher;
use Gt\WebEngine\Logic\Autoloader;
use Gt\WebEngine\Logic\LogicFactory;
use Gt\WebEngine\Response\ApiResponse;
use Gt\WebEngine\Response\PageResponse;
use Gt\WebEngine\Route\ApiRouter;
use Gt\WebEngine\Route\PageRouter;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;

class Lifecycle {
	/** @var Config */
	protected static $config;
	/** @var ServerInfo */
	protected static $serverInfo;
	/** @var Input */
	protected static $input;
	/** @var Cookie */
	protected static $cookie;
	/** @var Session */
	protected static $session;

	/** @var Request */
	protected static $request;
	/** @var Response */
	protected static $response;
	/** @var Router */
	protected static $router;
	/** @var Dispatcher */
	protected static $dispatcher;

	/**
	 * The start of the application's lifecycle. This function breaks the lifecycle down
	 * into its different functions, in order.
	 */
	public static function start():void {
		session_start();
		self::createCoreObjects();
		self::protectGlobals();
		self::createRequestResponse();
		self::createRouter();
		self::attachAutoloaders();
		self::dispatch();
		self::finish();
	}

	/**
	 * The "Core" objects within the WebEngine are encapsulated abstractions to core PHP
	 * functionality:
	 * - Config is used to retrieve configuration via config.ini and environment variables
	 * - Input is used to take user input through the querystring and posted form fields
	 * - Cookie is used to get and set cookies
	 * - Session is used to get and set persistent state data
	 */
	public static function createCoreObjects():void {
		self::$config = new Config($_ENV);
		self::$serverInfo = new ServerInfo($_SERVER);
		self::$input = new Input($_GET, $_POST, $_FILES);
		self::$cookie = new Cookie($_COOKIE);
		self::$session = new Session($_SESSION);
	}

	/**
	 * By default, PHP passes all sensitive user information around in global variables,
	 * available for reading and modification in any code, including third party libraries.
	 *
	 * All global variables are replaced with objects that alert the developer of their
	 * protection and encapsulation through GlobalStub objects.
	 *
	 * @see https://php.gt/globals
	 */
	public static function protectGlobals() {
		// TODO: Merge whitelist from config
		$whitelist = [
			"_COOKIE" => ["XDEBUG_SESSION"],
		];
		$globalsAfterRemoval = Protection::removeGlobals(
			$GLOBALS,
			$whitelist
		);
		Protection::overrideInternals(
			$globalsAfterRemoval,
			$_ENV,
			$_SERVER,
			$_GET,
			$_POST,
			$_FILES,
			$_COOKIE,
			$_SESSION
		);
	}

	/**
	 * The two most important parts of the application's lifecycle: the request and the response
	 * from and to the client. There are different types of request and response, depending on
	 * how the application is being used, so factory methods are used to create the correct
	 * type of request according to the server info. At this stage in the lifecycle, objects are
	 * only created, executing their logic when dispatched later.
	 */
	public static function createRequestResponse():void {
		self::$request = RequestFactory::create(
			self::$serverInfo,
			self::$input->getStream()
		);
		ResponseFactory::registerResponseClass(
			PageResponse::class,
			"text/html"
		);
		ResponseFactory::registerResponseClass(
			ApiResponse::class,
			"application/json",
			"application/xml"
		);
		self::$response = ResponseFactory::create(self::$request);
	}

	/**
	 * The router object is used to link the incoming request to the correct view/logic files
	 * within the application's directory. At this stage of the lifecycle the object is only
	 * created, executing its logic when dispatched later.
	 */
	public static function createRouter():void {
		RouterFactory::registerRouterClassForResponse(
			PageRouter::class,
			PageResponse::class
		);
		RouterFactory::registerRouterClassForResponse(
			ApiRouter::class,
			ApiResponse::class
		);

		self::$router = RouterFactory::create(
			self::$request,
			self::$response,
			self::$serverInfo->getDocumentRoot()
		);
	}

	public static function attachAutoloaders() {
		$logicAutoloader = new Autoloader(
			"App", // TODO: Load this from Config.
			self::$serverInfo->getDocumentRoot()
		);

		spl_autoload_register(
			[$logicAutoloader, "autoload"],
			true
		);
	}

	/**
	 * Now all of the essential objects of the application are created, the dispatcher will
	 * handle the request, build up the response and dispatch the relevant objects where they
	 * need to go.
	 */
	public static function dispatch():void {
		try {
			self::$dispatcher = DispatcherFactory::create(
				self::$router,
				self::$config,
				self::$serverInfo,
				self::$input,
				self::$cookie,
				self::$session
			);
			self::$dispatcher->handle(
				self::$request,
				self::$response
			);
		}
		catch(HttpError\NotFoundException $exception) {
			http_response_code(404);
			// TODO: Load provided 404 page - might also have code in it!
		}
		catch(HttpError\InternalServerErrorException $exception) {
			http_response_code(500);
			// TODO: Load provided error page.
		}
	}

	/**
	 * The final part of the lifecycle is the finish function. This is where the response is
	 * finally output to the client, followed by any tidy-up code required.
	 */
	public static function finish():void {
		echo self::$response;
	}
}
