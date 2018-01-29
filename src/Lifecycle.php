<?php
namespace Gt\WebEngine;

use Grpc\Server;
use Gt\Config\Config;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Http\Stream;
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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The fundamental purpose of any PHP framework is to provide a mechanism for generating an
 * HTTP response for an incoming HTTP request. Because this is such a common requirement, the
 * PHP Framework Interop Group have specified a "PHP standards recommendation" (PSR) to help
 * define the expected contract between the components of a web framework. The PSR that defines
 * the common interfaces for HTTP server request handlers is PSR-15.
 *
 * @see https://github.com/PhpGt/WebEngine/wiki/HTTP-Middleware
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md
 */
class Lifecycle implements MiddlewareInterface {
	/**
	 * The start of the application's lifecycle. This function breaks the lifecycle down
	 * into its different functions, in order.
	 */
	public function start():void {
		$config = new Config($_ENV);
		$serverInfo = new ServerInfo($_SERVER);
		$input = new Input($_GET, $_POST, $_FILES);
		$cookie = new Cookie($_COOKIE);
		$session = new Session($_SESSION);

		session_start();
		$this->protectGlobals();
		$this->attachAutoloaders($serverInfo->getDocumentRoot());

		$request = $this->createServerRequest(
			$serverInfo,
			$input->getStream()
		);
		$router = $this->createRouter(
			$request,
			$serverInfo->getDocumentRoot()
		);
		$dispatcher = $this->createDispatcher();

		$response = $this->process($request, $dispatcher);
		$this->finish($response);
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
	public function protectGlobals() {
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

	public function attachAutoloaders(string $documentRoot) {
		$logicAutoloader = new Autoloader(
			"App", // TODO: Load this from Config.
			$documentRoot
		);

		spl_autoload_register(
			[$logicAutoloader, "autoload"],
			true
		);
	}

	public function createServerRequest(
		ServerInfo $serverInfo,
		StreamInterface $body
	):ServerRequestInterface {
		return RequestFactory::createServerRequest(
			$serverInfo,
			$body
		);
	}

	public function createRouter(RequestInterface $request, string $documentRoot):Router {
		return RouterFactory::create(
			$request,
			$documentRoot
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

	public function createDispatcher():Dispatcher {
		$dispatcher = DispatcherFactory::create();
		return $dispatcher;
	}

	/**
	 * Process an incoming server request and return a response, optionally delegating
	 * response creation to a handler.
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	):ResponseInterface {

	}

	/**
	 * The final part of the lifecycle is the finish function. This is where the response is
	 * finally output to the client, followed by any tidy-up code required.
	 */
	public static function finish(ResponseInterface $response):void {
		echo $response;
	}
}
