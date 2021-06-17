<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Cookie\CookieHandler;
use Gt\Csrf\SessionTokenStore;
use Gt\Csrf\TokenStore;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Http\Header\Headers;
use Gt\Http\Response;
use Gt\Http\ResponseStatusException\AbstractResponseStatusException;
use Gt\Http\ResponseStatusException\Redirection\AbstractRedirectionException;
use Gt\Http\ServerInfo;
use Gt\Http\RequestFactory;
use Gt\Logger\Log;
use Gt\WebEngine\Debug\Timer;
use Gt\WebEngine\FileSystem\BasenameNotFoundException;
use Gt\WebEngine\Route\PageRouter;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Gt\Input\Input;
use Gt\ProtectedGlobal\Protection;
use Gt\Session\Session;
use Gt\Session\SessionSetup;
use Gt\WebEngine\Dispatch\Dispatcher;
use Gt\WebEngine\Logic\Autoloader;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;
use SplFileObject;

/**
 * The fundamental purpose of any PHP framework is to provide a mechanism for
 * generating an HTTP response for an incoming HTTP request. Because this is
 * such a common requirement, the PHP Framework Interop Group have specified a
 * "PHP standards recommendation" (PSR) to help define the expected contract
 * between the components of a web framework. The PSR that defines the common
 * interfaces for HTTP server request handlers is PSR-15.
 *
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md
 *
 * This Lifecycle class implements the PSR-15 MiddlewareInterface, which defines
 * a single "process" function that takes a Request and a RequestHandler,
 * and in turn returns a Response.
 *
 * @link https://github.com/PhpGt/WebEngine/wiki/HTTP-Middleware
 *
 * At the start of the lifecycle, when using an ordinary HTTP server such as
 * Apache or Nginx, there isn't actually any Request object available yet:
 * that's the job of the "start" function. It will create an appropriate
 * Request object and pass it to the "process" function for handling.
 *
 * An optional execution would be to use a PHP-based HTTP server that provides
 * its own Request object, and pass it directly to the "process" function.
 */
class Lifecycle implements MiddlewareInterface {
	public function start():void {
// The first thing that's done within the WebEngine lifecycle is start a timer.
// This timer is only used again at the very end of the call, when finish() is
// called - at which point the entire duration of the request is logged out (and
// slow requests are highlighted as a NOTICE).
		$timer = new Timer();

// Starting the output buffer done before any logic is executed, so any calls
// to any area of code will not accidentally send output to the client.
		ob_start();

// A PSR-7 HTTP Request object is created from the current global state, ready
// for processing by the Handler.
		$requestFactory = new RequestFactory();
		$request = $requestFactory->createServerRequestFromGlobalState(
			$_SERVER,
			$_FILES,
			$_GET,
			$_POST,
		);

// The handler is an individual component that processes a request and produces
// a response, as defined by PSR-7. It's where all your applications logic is
// executed - the brain of WebEngine.
		$handler = new RequestHandler();

// The request and request handler are passed to the PSR-15 process function,
// which will return our PSR-7 HTTP Response.
		$response = $this->process($request, $handler);

// All logic will have executed at this point, so we clean the output buffer in
// case there was any accidental data echoed to the page.
		$buffer = ob_get_clean();
// Now we can finish the HTTP lifecycle by providing the HTTP response for
// outputting to the browser, along with the buffer so we can display the
// contents in a debug area.
		$this->finish(
			$response,
			$buffer,
			$timer,
			$handler->getConfigSection("app")
		);
	}

	/**
	 * Process an incoming server request and return a response,
	 * delegating response creation to a handler.
	 *
	 * @throws BasenameNotFoundException
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler
	):ResponseInterface {
		return $handler->handle($request);
	}

	public function finish(
		ResponseInterface $response,
		string $buffer,
		Timer $timer,
		ConfigSection $appConfig
	):void {
		http_response_code($response->getStatusCode());

		foreach($response->getHeaders() as $key => $value) {
			header("$key: $value", true);
		}

		$renderTo = new SplFileObject($appConfig->getString("render_to"), "w");

		$bufferSize = $appConfig->getInt("render_buffer_size");
		$bufferLen = strlen($buffer);
		for($i = 0; $i < $bufferLen; $i += $bufferSize) {
			$renderTo->fwrite(substr($buffer, $i, $bufferSize));
		}

		$body = $response->getBody();
		while(!$body->eof()) {
			$renderTo->fwrite($body->read($bufferSize));
		}

// The very last thing that's done before the script ends is to stop the Timer,
// so we know exactly how long the request-response lifecycle has taken.
		$timer->stop();
		$delta = number_format($timer->getDelta(), 2);
		if($delta >= $appConfig->getFloat("slow_delta")) {
			Log::warning("Lifecycle end with VERY SLOW delta time: $delta seconds. https://www.php.gt/webengine/slow-delta");
		}
		elseif($delta >= $appConfig->getFloat("very_slow_delta")) {
			Log::notice("Lifecycle end with SLOW delta time: $delta seconds. https://www.php.gt/webengine/slow-delta");
		}
		else {
			Log::debug("Lifecycle end, delta time: $delta seconds.");
		}
	}















	/**
	 * By default, PHP passes all sensitive user information around in
	 * global variables, available for reading and modification in any code,
	 * including third party libraries.
	 *
	 * WebEngine applications are provided an object-oriented alternative to
	 * globals, and all global variables are replaced with objects that
	 * alert the developer of their protection and encapsulation through
	 * GlobalStub objects.
	 *
	 * @link https://www.php.gt/webengine/globals
	 * @noinspection PhpExpressionResultUnusedInspection
	 * @return array<string, array<string, string>
	 */
	private function _old_protectGlobals():array {
		// TODO: Merge whitelist from config
		$whitelist = [
			"_COOKIE" => ["XDEBUG_SESSION"],
		];
		$_SERVER;
		$_ENV;
		$cloned = $GLOBALS;

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

		return $cloned;
	}

	/**
	 * The start of the application's lifecycle. This function breaks the
	 * lifecycle down into its different functions, in order.
	 */
	public function _old_start():ResponseInterface {
// Starting the output buffer is the first task, so any calls to any area of
// code will not accidentally send output to the client.
		ob_start();
// ServerInfo is a class supplied by the Http library. It is an object oriented
// representation of the $_SERVER super-global and is used to encapsulate the
// values and provide PSR-7 compatibility.
// @link https://www/php.gt/http
		$server = new ServerInfo($_SERVER);
		$projectRootDir = dirname($server->getDocumentRoot());
// Here we ensure that all future executing code is done with the current
// directory set to the directory containing the document root - also known
// as: the project root directory.
		chdir($projectRootDir);

// The ConfigFactory builds a configuration object that takes values from the
// project's config.ini, merged over the config.default.ini which is
// supplied by WebEngine.
// @link https://www.php.gt/config
		$config = ConfigFactory::createForProject(
			$projectRootDir,
			implode(DIRECTORY_SEPARATOR, [
				dirname(__DIR__),
				"config.default.ini",
			])
		);

// The Input class encapsulates all user input and provides a mechanism for
// executing callback functions when input is received.
// @link https://www.php.gt/input
		$input = new Input($_GET, $_POST, $_FILES);
// The CookieJar is an object oriented interface to the HTTP cookies.
// @link https://www.php.gt/cookie
		$cookie = new CookieHandler($_COOKIE);

// Sessions are handled using a configurable session handler, so it's easy to
// use a file on disk, SQLite, memcached, etc.
// @link https://www.php.gt/session
		$sessionHandler = SessionSetup::attachHandler(
			$config->get("session.handler")
		);
		$sessionConfig = $config->getSection("session");
		$sessionId = $cookie[$sessionConfig["name"]];
		$sessionHandler = new Session(
			$sessionHandler,
			$sessionConfig,
			$sessionId
		);

// If the project uses a database, its connections and queries are handled using
// the Database library. The connections are lazy-loaded, so the objects can be
// constructed here without adding a delay.
// @link https://www.php.gt/database
		$databaseSettings = new Settings(
			$config->get("database.query_directory"),
			$config->get("database.driver"),
			$config->get("database.schema"),
			$config->get("database.host"),
			$config->get("database.port"),
			$config->get("database.username"),
			$config->get("database.password")
		);
		$database = new Database($databaseSettings);

// At this point, no future code should have direct access to super-global
// values, so code can be encapsulated and access can be controlled.
// @link https://www.php.gt/protectedglobal
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
			$server->getDocumentRoot(),
			$config->get("app.default_content_type")
		);

		$csrfProtection = new SessionTokenStore(
			$sessionHandler->getStore(
				"gt.csrf",
				true
			)
		);

		if($router instanceof PageRouter) {
			$csrfProtection->processAndVerify(
				$input->getAll(Input::DATA_BODY)
			);
		}

		try {
			$dispatcher = $this->createDispatcher(
				$config,
				$server,
				$input,
				$cookie,
				$sessionHandler,
				$database,
				$router,
				$csrfProtection,
				new Headers($request->getHeaders())
			);

			$response = $this->process($request, $dispatcher);
		}
		catch(\Exception $exception) {
			if($exception instanceof AbstractResponseStatusException) {
				$code = $exception->getHttpCode();
			}
			elseif($exception instanceof BasenameNotFoundException) {
				$code = 404;
			}
			else {
				$code = 500;
			}

			$uri = $request->getUri();
			$dispatcher->overrideRouterUri($uri->withPath("_$code"));
			try {
				$response = $this->process($request, $dispatcher);
			}
			catch(\Exception $ignoreException) {
				// TODO: Log exception here.
				$response = new Response($code);
			}

			$response = $response->withStatus($code);
			if($exception instanceof AbstractRedirectionException) {
				$response = $response->withHeader(
					"Location",
					$exception->getMessage()
				);
			}
		}

		$buffer = ob_get_clean();
		return $this->finish($response, $buffer, $render);
	}

	public function _old_attachAutoloaders(string $documentRoot, ConfigSection $config) {
		$logicAutoloader = new Autoloader(
			$config["namespace"],
			$documentRoot
		);

		spl_autoload_register(
			[$logicAutoloader, "autoload"],
			true
		);
	}

	public function _old_createServerRequest(
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

	public function _old_createRouter(
		RequestInterface $request,
		string $documentRoot,
		string $defaultContentType
	):Router {
		$factory = new RouterFactory($defaultContentType);
		return $factory->create(
			$request,
			$documentRoot
		);
	}

	public function _old_createDispatcher(
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookie,
		Session $session,
		Database $database,
		Router $router,
		TokenStore $csrfProtection,
		Headers $headers
	):Dispatcher {
		return DispatcherFactory::create(
			$config,
			$serverInfo,
			$input,
			$cookie,
			$session,
			$database,
			$router,
			$csrfProtection,
			$headers
		);
	}

	/**
	 * The final part of the lifecycle is the finish function.
	 * This is where the response is finally output to the client,
	 * after the response headers are appended from any calls to the native
	 * header function.
	 */
	public static function _old_finish(
		ResponseInterface $response,
		string $buffer = "",
		bool $render = true
	):ResponseInterface {
		http_response_code($response->getStatusCode());
		foreach($response->getHeaders() as $key => $value) {
			header("$key: $value");
		}

		if($render) {
			echo $buffer;
			echo $response->getBody();
		}

		return $response;
	}
}
