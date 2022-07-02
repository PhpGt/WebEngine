<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\PartialContent;
use Gt\DomTemplate\PartialContentDirectoryNotFoundException;
use Gt\DomTemplate\PartialExpander;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Input\InputData\InputData;
use Gt\Logger\LogConfig;
use Gt\Logger\LogHandler\FileHandler;
use Gt\Logger\LogHandler\StdOutHandler;
use Gt\Logger\LogHandler\StreamHandler;
use Gt\Routing\BaseRouter;
use Gt\Routing\LogicStream\LogicStreamWrapper;
use Gt\Routing\Path\DynamicPath;
use Gt\ServiceContainer\Container;
use Gt\ServiceContainer\Injector;
use Gt\Session\Session;
use Gt\Session\SessionSetup;
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
		$response = new Response();
		$requestUri = $request->getUri();
		$uriPath = $requestUri->getPath();

// Force trailing slashes in URLs. This is useful for consistency, but also
// helps identify that WebEngine requests do not match an actual static file, as
// file requests will never end in a slash. Another benefit is that links can
// behave relatively (e.g. <a href="./something/"> )
// 307 is used here to preserve any POST data that may be in the request.
		if(!str_ends_with($uriPath, "/")) {
			return $response
				->withHeader("Location", $requestUri->withPath($requestUri->getPath() . "/"))
				->withStatus(307);
		}

		$serviceContainer = new Container();
		$serviceContainer->set($request);
		$serviceContainer->addLoaderClass(
			new DefaultServiceLoader(
				$this->config,
				$request,
				$serviceContainer
			)
		);
		$serviceContainer->set($this->config);
		$customServiceContainerClassName = implode("\\", [
			$this->config->get("app.namespace"),
			$this->config->get("app.service_loader"),
		]);
		if(class_exists($customServiceContainerClassName)) {
			$constructorArgs = [];
			if(is_a($customServiceContainerClassName, DefaultServiceLoader::class, true)) {
				$constructorArgs = [
					$this->config,
					$request,
					$serviceContainer,
				];
			}

			$serviceContainer->addLoaderClass(
				new $customServiceContainerClassName(
					...$constructorArgs
				)
			);
		}

		$server = new ServerInfo($_SERVER);
		$serviceContainer->set($server);

		$router = $this->createRouter($serviceContainer);
		$router->route($request);

		$viewClass = $router->getViewClass() ?? NullView::class;
		/** @var BaseView $view */
		$view = new $viewClass($response->getBody());

		$viewAssembly = $router->getViewAssembly();
		$logicAssembly = $router->getLogicAssembly();

		$dynamicPath = new DynamicPath(
			$uriPath,
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

// TODO: Set a Session loader here, so the CSRF handler can use it.

		if($viewModel instanceof HTMLDocument) {
			try {
				$partial = new PartialContent(implode(DIRECTORY_SEPARATOR, [
					getcwd(),
					$this->config->getString("view.component_directory")
				]));
				$componentExpander = new ComponentExpander($viewModel, $partial);
				$componentExpander->expand();
			}
			catch(PartialContentDirectoryNotFoundException) {}

			try {
				$partial = new PartialContent(implode(DIRECTORY_SEPARATOR, [
					getcwd(),
					$this->config->getString("view.partial_directory")
				]));

				$partialExpander = new PartialExpander($viewModel, $partial);
				$partialExpander->expand();
			}
			catch(PartialContentDirectoryNotFoundException) {}

			$dynamicUri = $dynamicPath->getUrl("page/");
			$dynamicUri = str_replace("/", "--", $dynamicUri);
			$dynamicUri = str_replace("@", "_", $dynamicUri);
			$viewModel->body->classList->add("uri" . $dynamicUri);
			$bodyDirClass = "dir";
			foreach(explode("--", $dynamicUri) as $i => $pathPart) {
				if($i === 0) {
					continue;
				}
				$bodyDirClass .= "--$pathPart";
				$viewModel->body->classList->add($bodyDirClass);
			}

//			ini_set('session.serialize_handler', 'php_serialize');
			$sessionConfig = $this->config->getSection("session");
			$sessionId = $_COOKIE[$sessionConfig["name"]] ?? null;
			$sessionHandler = SessionSetup::attachHandler(
				$sessionConfig->getString("handler")
			);
			$session = new Session(
				$sessionHandler,
				$sessionConfig,
				$sessionId
			);
			$serviceContainer->set($session);

//			TODO: Complete CSRF implementation - maybe use its own cookie?
//			/** @var Session $session */
//			$session = $serviceContainer->get(Session::class);
//			$csrfTokenStore = new SessionTokenStore($session->getStore("csrf", true));
//
//			if($request->getMethod() === "POST") {
//				$csrfTokenStore->processAndVerify($_POST);
//			}
//
//			$protector = new HTMLDocumentProtector($viewModel, $csrfTokenStore);
//			$protector->protectAndInject();
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
		$logicExecutor->invoke("go_after");

		/** @var DocumentBinder $documentBinder */
		$documentBinder = $serviceContainer->get(DocumentBinder::class);
		$documentBinder->cleanDatasets();

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
