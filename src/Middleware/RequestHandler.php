<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Logger\Log;
use Gt\Logger\LogConfig;
use Gt\Logger\LogHandler\FileHandler;
use Gt\Logger\LogHandler\StdOutHandler;
use Gt\Logger\LogHandler\StreamHandler;
use Gt\Routing\BaseRouter;
use Gt\Routing\LogicStream\LogicStreamNamespace;
use Gt\Routing\LogicStream\LogicStreamWrapper;
use Gt\Routing\Path\PathMatcher;
use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\Injector;
use Gt\WebEngine\DefaultRouter;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\View\BaseView;
use Gt\WebEngine\View\NullView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface {
	private Config $config;

	public function __construct(
	) {
		$this->config = ConfigFactory::createForProject(
			getcwd(),
			getcwd() . "/vendor/phpgt/webengine/config.default.ini"
		);
		$this->setupLogger($this->config->getSection("logger"));
		stream_wrapper_register(
			"gt-logic-stream",
			LogicStreamWrapper::class
		);
	}

	public function handle(
		ServerRequestInterface $request
	):ResponseInterface {
// TODO: Handle 404s.
// TODO: Build up the gt-logic-stream file properly (handle user-defined namespaces, do functions, whatever...
// TODO: Extract a DI building class to build up all the classes for the container, so the developer can also create their own setup functions (and handle a way of only executing it under certain request conditions).
// TODO: DomTemplate stuff - hook up the hello, you!
// TODO: Database?
// TODO: CSRF?
		$serviceContainer = new Container();
		$serviceContainer->set($request);
		$serviceContainer->set(new PathMatcher(getcwd()));

		$router = $this->createRouter($serviceContainer);
		$router->route($request);
		$response = new Response();

		$viewClass = $router->getViewClass() ?? NullView::class;
		/** @var BaseView $view */
		$view = new $viewClass($response->getBody());

		$viewAssembly = $router->getViewAssembly();
		$logicAssembly = $router->getLogicAssembly();

		if(count($viewAssembly) === 0) {
			$response = $response->withStatus(404);
		}

		foreach($viewAssembly as $viewFile) {
			$view->addViewFile($viewFile);
		}
		$viewModel = $view->createViewModel();
		$serviceContainer->set($viewModel);

		$injector = new Injector($serviceContainer);

		foreach($logicAssembly as $logicFile) {
			require("gt-logic-stream://$logicFile");
			$ns = new LogicStreamNamespace($logicFile);
			$fqns = LogicStreamWrapper::NAMESPACE_PREFIX . $ns;
			$injector->invoke(null, "$fqns\\go");
		}

		$view->stream($viewModel);
		return $response;
	}

	public function getConfigSection(string $sectionName):ConfigSection {
		return $this->config->getSection($sectionName);
	}

	private function setupLogger(ConfigSection $logConfig):void {
		$type = $logConfig->getString("type");
		$path = $logConfig->getString("path");
		$level = $logConfig->getString("level");
		$timestampFormat = $logConfig->getString("timestamp_format");
		$logFormat = explode("\\t", $logConfig->getString("log_format"));
		$separator = $logConfig->getString("separator");
		$newLine = $logConfig->getString("newline");
		$logHandler = match($type) {
			"file" => new FileHandler($path, $timestampFormat, $logFormat, $separator, $newLine),
			"stream" => new StreamHandler($path),
			default => new StdOutHandler()
		};
		LogConfig::addHandler($logHandler, $level);
	}

	private function createRouter(Container $container):BaseRouter {
		$routerConfig = $this->config->getSection("router");
		$namespace = $this->config->getString("app.namespace");
		$appRouterFile = $routerConfig->getString("router_file");
		$appRouterClass = $routerConfig->getString("router_class");
		$defaultRouterFile = dirname(dirname(__DIR__)) . "/router.default.php";

		if(file_exists($appRouterFile)) {
			require($appRouterFile);
			$class = "\\$namespace\\$appRouterClass";
		}
		else {
			require($defaultRouterFile);
			$class = "\\Gt\\WebEngine\\DefaultRouter";
		}

		/** @var BaseRouter $router */
		$router = new $class($routerConfig);
		$router->setContainer($container);
		return $router;
	}
}
