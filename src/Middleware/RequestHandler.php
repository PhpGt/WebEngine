<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentDirectoryNotFoundException;
use Gt\DomTemplate\PartialExpander;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Input\InputData\InputData;
use Gt\Logger\LogConfig;
use Gt\Logger\LogHandler\FileHandler;
use Gt\Logger\LogHandler\StdOutHandler;
use Gt\Logger\LogHandler\StreamHandler;
use Gt\Routing\BaseRouter;
use Gt\Routing\LogicStream\LogicStreamWrapper;
use Gt\Routing\Path\DynamicPath;
use Gt\Routing\Path\PathMatcher;
use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\Injector;
use Gt\WebEngine\Logic\AppAutoloader;
use Gt\WebEngine\Logic\LogicExecutor;
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
		$this->setupLogger(
			$this->config->getSection("logger")
		);

		$appAutoloader = new AppAutoloader(
			$this->config->get("app.namespace"),
			$this->config->get("app.class_dir"),
		);
		$appAutoloader->setup();

		stream_wrapper_register(
			"gt-logic-stream",
			LogicStreamWrapper::class
		);
	}

	public function handle(
		ServerRequestInterface $request
	):ResponseInterface {
// TODO: Handle 404s.
// TODO: CSRF?
		$serviceContainer = new Container();
		$serviceContainer->set($request);
		$serviceContainer->set(new PathMatcher(getcwd()));
		$serviceContainer->addLoaderClass(
			new DefaultServiceLoader(
				$this->config,
				$request,
				$serviceContainer
			)
		);
		$customServiceContainerClassName = implode("\\", [
			$this->config->get("app.namespace"),
			$this->config->get("app.service_loader"),
		]);
		if(class_exists($customServiceContainerClassName)) {
			$constructorArgs = [];
			if(is_a($customServiceContainerClassName, DefaultServiceLoader::class, true)) {
				$constructorArgs = [
					$this->config,
					$serviceContainer,
				];
			}

			$serviceContainer->addLoaderClass(
				new $customServiceContainerClassName(
					...$constructorArgs
				)
			);
		}

		$router = $this->createRouter($serviceContainer);
		$router->route($request);
		$response = new Response();

		$viewClass = $router->getViewClass() ?? NullView::class;
		/** @var BaseView $view */
		$view = new $viewClass($response->getBody());

		$viewAssembly = $router->getViewAssembly();
		$logicAssembly = $router->getLogicAssembly();
		$dynamicPath = new DynamicPath(
			$request->getUri()->getPath(),
			$viewAssembly,
			$logicAssembly,
		);
		$serviceContainer->set($dynamicPath);

		if(count($viewAssembly) === 0) {
			$response = $response->withStatus(404);
		}

		foreach($viewAssembly as $viewFile) {
			$view->addViewFile($viewFile);
		}
		$viewModel = $view->createViewModel();
		$serviceContainer->set($viewModel);

		if($viewModel instanceof HTMLDocument) {
			try {
				$modularContent = new ModularContent(implode(DIRECTORY_SEPARATOR, [
					getcwd(),
					$this->config->getString("view.component_directory")
				]));
				$componentExpander = new ComponentExpander($viewModel, $modularContent);
				$componentExpander->expand();
			}
			catch(ModularContentDirectoryNotFoundException) {}

			try {
				$modularContent = new ModularContent(implode(DIRECTORY_SEPARATOR, [
					getcwd(),
					$this->config->getString("view.partial_directory")
				]));

				$partialExpander = new PartialExpander($viewModel, $modularContent);
				$partialExpander->expand();
			}
			catch(ModularContentDirectoryNotFoundException) {}

			$bodyClass = "uri";
			foreach(explode("/", $request->getUri()->getPath()) as $i => $pathPart) {
				if($i === 0) {
					continue;
				}
				$bodyClass .= "--$pathPart";
				$viewModel->body->classList->add($bodyClass);
			}
		}

// TODO: Kill globals.
		$input = new Input($_GET, $_POST, $_FILES);
		$serviceContainer->set($input);

		$injector = new Injector($serviceContainer);

		$logicExecutor = new LogicExecutor(
			$logicAssembly,
			$injector,
			$this->config->getString("app.namespace")
		);
		$input->when("do")->call(
			fn(InputData $data) => $logicExecutor->invoke(
				"do_" . str_replace("-", "_", $data->getString("do"))
			)
		);
		$logicExecutor->invoke("go");

		/** @var DocumentBinder $documentBinder */
		$documentBinder = $serviceContainer->get(DocumentBinder::class);
		$documentBinder->cleanBindAttributes();

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
