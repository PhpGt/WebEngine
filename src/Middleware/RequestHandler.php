<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\HTMLAttributeCollection;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\ModularContent;
use Gt\DomTemplate\ModularContentExpander;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\TableBinder;
use Gt\DomTemplate\TemplateCollection;
use Gt\Http\Response;
use Gt\Input\Input;
use Gt\Input\InputData\InputData;
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
use Gt\WebEngine\Logic\LogicExecutor;
use Gt\WebEngine\Logic\LogicProjectNamespace;
use Gt\WebEngine\View\BaseView;
use Gt\WebEngine\View\HTMLView;
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
		if($viewModel instanceof HTMLDocument) {
			$modularContent = new ModularContent(implode(DIRECTORY_SEPARATOR, [
				getcwd(),
				$this->config->getString("view.component_directory")
			]));
			$componentExpander = new ComponentExpander($viewModel, $modularContent);
			$componentExpander->expand();

			$htmlAttributeBinder = new HTMLAttributeBinder();
			$htmlAttributeCollection = new HTMLAttributeCollection();
			$placeholderBinder = new PlaceholderBinder();

			$elementBinder = new ElementBinder(
				$htmlAttributeBinder,
				$htmlAttributeCollection,
				$placeholderBinder
			);
			$tableBinder = new TableBinder();
			$templateCollection = new TemplateCollection($viewModel);
			$listBinder = new ListBinder($templateCollection);

			$documentBinder = new DocumentBinder(
				$viewModel,
				[], // TODO: Get domtemplate config as array.
				$elementBinder,
				$placeholderBinder,
				$tableBinder,
				$listBinder,
				$templateCollection
			);

			$serviceContainer->set($htmlAttributeBinder);
			$serviceContainer->set($htmlAttributeCollection);
			$serviceContainer->set($placeholderBinder);
			$serviceContainer->set($elementBinder);
			$serviceContainer->set($tableBinder);
			$serviceContainer->set($templateCollection);
			$serviceContainer->set($listBinder);
			$serviceContainer->set($documentBinder);
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
// TODO: Automatically refresh "do" functions.
		$input->when("do")->call(
			fn(InputData $data) => $logicExecutor->invoke(
				"do_" . $data->getString("do")
			)
		);
		$logicExecutor->invoke("go");

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
