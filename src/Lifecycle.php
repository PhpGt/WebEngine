<?php
namespace Gt\WebEngine;

use Gt\Config\Config;

use Gt\Cookie\CookieHandler;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\ProtectedGlobal\Protection;
use Gt\Session\Session;
use Gt\Http\RequestFactory;
use Gt\WebEngine\Dispatch\Dispatcher;
use Gt\WebEngine\Logic\Autoloader;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
		$server = new ServerInfo($_SERVER);
		$input = new Input($_GET, $_POST, $_FILES);
		$cookie = new CookieHandler($_COOKIE);

		session_start();
		$session = new Session($_SESSION);

		$this->protectGlobals();
		$this->attachAutoloaders($server->getDocumentRoot());

		$request = $this->createServerRequest(
			$server,
			$input,
			$cookie
		);
		$router = $this->createRouter(
			$request,
			$server->getDocumentRoot()
		);
		$dispatcher = $this->createDispatcher(
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$router
		);

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
		Input $input,
		CookieHandler $cookieHandler
	):ServerRequestInterface {
		return RequestFactory::createServerRequest(
			$serverInfo,
			$input,
			$cookieHandler
		);
	}

	public function createRouter(RequestInterface $request, string $documentRoot):Router {
		return RouterFactory::create(
			$request,
			$documentRoot
		);
	}

	public function createDispatcher(
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookie,
		Session $session,
		Router $router
	):Dispatcher {
		$dispatcher = DispatcherFactory::create(
			$config,
			$serverInfo,
			$input,
			$cookie,
			$session,
			$router
		);
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
		return $handler->handle($request);
	}

	/**
	 * The final part of the lifecycle is the finish function. This is where the response is
	 * finally output to the client, followed by any tidy-up code required.
	 */
	public static function finish(ResponseInterface $response):void {
		echo $response->getBody();
	}
}
