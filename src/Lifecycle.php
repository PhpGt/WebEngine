<?php
namespace Gt\WebEngine;

use Gt\Config\Config;

use Gt\Config\ConfigSection;
use Gt\Cookie\CookieHandler;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\ProtectedGlobal\Protection;
use Gt\Session\Session;
use Gt\Http\RequestFactory;
use Gt\Session\SessionSetup;
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
		$server = new ServerInfo($_SERVER);
		$config = new Config(dirname($server->getDocumentRoot()));
		$config->setDefault(dirname(__DIR__));
		$input = new Input($_GET, $_POST, $_FILES);
		$cookie = new CookieHandler($_COOKIE);

		$handler = SessionSetup::attachHandler(
			$config->get("session.handler")
		);
		$sessionConfig = $config->getSection("session");
		$sessionId = $cookie[$sessionConfig["name"]];
		$session = new Session(
			$handler,
			$sessionConfig,
			$sessionId
		);

		$databaseSettings = new Settings(
			$config->get("database.query_directory"),
			$config->get("database.dsn"),
			$config->get("database.schema"),
			$config->get("database.host"),
			$config->get("database.port"),
			$config->get("database.username"),
			$config->get("database.password")
		);
		$database = new Database($databaseSettings);

		$this->protectGlobals();
		$this->attachAutoloaders(
			$server->getDocumentRoot(),
			$config->getSection("app")
		);

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
			$database,
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

	public function attachAutoloaders(string $documentRoot, ConfigSection $config) {
		$logicAutoloader = new Autoloader(
			$config["namespace"],
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
		Database $database,
		Router $router
	):Dispatcher {
		$dispatcher = DispatcherFactory::create(
			$config,
			$serverInfo,
			$input,
			$cookie,
			$session,
			$database,
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
