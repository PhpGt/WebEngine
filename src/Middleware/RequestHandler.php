<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Config\ConfigSection;
use Gt\Csrf\HTMLDocumentProtector;
use Gt\Csrf\SessionTokenStore;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\ComponentExpander;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\PartialContent;
use Gt\DomTemplate\PartialContentDirectoryNotFoundException;
use Gt\DomTemplate\PartialExpander;
use Gt\Http\Header\ResponseHeaders;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Http\StatusCode;
use Gt\Input\Input;
use Gt\Input\InputData\InputData;
use Gt\Logger\LogConfig;
use Gt\Logger\LogHandler\FileHandler;
use Gt\Logger\LogHandler\StdOutHandler;
use Gt\Logger\LogHandler\StreamHandler;
use Gt\ProtectedGlobal\Protection;
use Gt\Routing\Assembly;
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
	/** @var callable(ResponseInterface,ConfigSection) */
	protected $finishCallback;
	/** @var callable(string, string) */
	protected $obCallback;
	protected Container $serviceContainer;
	protected Injector $injector;
	protected ResponseInterface $response;
	protected Assembly $viewAssembly;
	protected Assembly $logicAssembly;
	protected DynamicPath $dynamicPath;
	protected HTMLDocument/*|NullViewModel*/ $viewModel;
	protected BaseView $view;

	public function __construct(
		protected readonly Config $config,
		callable $finishCallback,
		callable $obCallback,
	) {
		$this->finishCallback = $finishCallback;
		$this->obCallback = $obCallback;

		$this->setupLogger(
			$this->config->getSection("logger")
		);

		$appAutoloader = new AppAutoloader(
			$this->config->get("app.namespace"),
			$this->config->get("app.class_dir"),
		);
		$appAutoloader->setup();

		if(!in_array("gt-logic-stream", stream_get_wrappers())) {
			stream_wrapper_register(
				"gt-logic-stream",
				LogicStreamWrapper::class,
			);
		}
	}

	public function getConfigSection(string $sectionName):ConfigSection {
		return $this->config->getSection($sectionName);
	}

	public function getServiceContainer():Container {
		return $this->serviceContainer;
	}

	public function handle(
		ServerRequestInterface $request
	):ResponseInterface {
		$this->completeRequestHandling($request);
		return $this->response;
	}

	protected function completeRequestHandling(
		ServerRequestInterface $request,
		?Container $container = null,
	):void {
		if($container) {
			$this->serviceContainer = $container;
		}
		$this->setupResponse($request);
		$this->forceTrailingSlashes($request);
		$this->setupServiceContainer();

		if($container?->has(Input::class)) {
			$input = $container->get(Input::class);
		}
		else {
			$input = new Input($_GET, $_POST, $_FILES);
		}

		if($container?->has(ServerInfo::class)) {
			$serverInfo = $container->get(ServerInfo::class);
		}
		else {
			$serverInfo = new ServerInfo($_SERVER);
		}

		$this->serviceContainer->set(
			$this->config,
			$request,
			$this->response,
			$this->response->headers,
			$input,
			$serverInfo,
		);
		$this->injector = new Injector($this->serviceContainer);
		$this->handleRouting($request);
		if(!$this->serviceContainer->has(Session::class)) {
			$this->handleSession();
		}

		if($this->viewModel instanceof HTMLDocument) {
			$this->handleHTMLDocumentViewModel();
			$this->handleCsrf($request);
		}

		$this->handleProtectedGlobals();
		$this->handleLogicExecution();

// TODO: Why is this in the handle function?
		$documentBinder = $this->serviceContainer->get(DocumentBinder::class);
		$documentBinder->cleanDatasets();

		$this->view->stream($this->viewModel);

		$responseHeaders = $this->serviceContainer->get(ResponseHeaders::class);
		foreach($responseHeaders->asArray() as $name => $value) {
			$this->response = $this->response->withHeader(
				$name,
				$value,
			);
		}
	}

	protected function handleRouting(ServerRequestInterface $request) {
		$router = $this->createRouter($this->serviceContainer);
		$router->route($request);

		$viewClass = $router->getViewClass() ?? NullView::class;
		$this->view = new $viewClass($this->response->getBody());

		$this->viewAssembly = $router->getViewAssembly();
		$this->logicAssembly = $router->getLogicAssembly();

		$this->dynamicPath = new DynamicPath(
			$request->getUri()->getPath(),
			$this->viewAssembly,
			$this->logicAssembly,
		);

		$this->serviceContainer->set($this->dynamicPath);

		if(!$this->viewAssembly->containsDistinctFile()) {
			$this->response = $this->response->withStatus(StatusCode::NOT_FOUND);
		}

		foreach($this->viewAssembly as $viewFile) {
			$this->view->addViewFile($viewFile);
		}
		if($viewModel = $this->view->createViewModel()) {
			$this->serviceContainer->set($viewModel);
			$this->viewModel = $viewModel;
		}
	}

	protected function handleHTMLDocumentViewModel():void {
		try {
			$partial = new PartialContent(implode(DIRECTORY_SEPARATOR, [
				getcwd(),
				$this->config->getString("view.component_directory")
			]));
			$componentExpander = new ComponentExpander(
				$this->viewModel,
				$partial,
			);
			$componentExpander->expand();
		}
		catch(PartialContentDirectoryNotFoundException) {}

		try {
			$partial = new PartialContent(implode(DIRECTORY_SEPARATOR, [
				getcwd(),
				$this->config->getString("view.partial_directory")
			]));

			$partialExpander = new PartialExpander(
				$this->viewModel,
				$partial
			);
			$partialExpander->expand();
		}
		catch(PartialContentDirectoryNotFoundException) {}

		$dynamicUri = $this->dynamicPath->getUrl("page/");
		$dynamicUri = str_replace("/", "--", $dynamicUri);
		$dynamicUri = str_replace("@", "_", $dynamicUri);
		$this->viewModel->body->classList->add("uri" . $dynamicUri);
		$bodyDirClass = "dir";
		foreach(explode("--", $dynamicUri) as $i => $pathPart) {
			if($i === 0) {
				continue;
			}
			$bodyDirClass .= "--$pathPart";
			$this->viewModel->body->classList->add($bodyDirClass);
		}
	}

	protected function handleSession():void {
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
		$this->serviceContainer->set($session);
	}

	protected function handleCsrf(ServerRequestInterface $request):void {
		$shouldVerifyCsrf = true;
		$ignoredPathArray = explode(",", $this->config->getString("security.csrf_ignore_path") ?? "");
		foreach($ignoredPathArray as $ignoredPath) {
			if(empty($ignoredPath)) {
				continue;
			}

			if(str_contains($ignoredPath, "*")) {
				$pattern = strtr(rtrim($ignoredPath, "/"), [
					"*" => ".*",
				]);
				if(preg_match("|$pattern|", rtrim($request->getUri()->getPath(), "/"))) {
					$shouldVerifyCsrf = false;
				}
			}
			else {
				if(rtrim($request->getUri()->getPath(), "/") === rtrim($ignoredPath, "/")) {
					$shouldVerifyCsrf = false;
				}
			}
		}

		if($shouldVerifyCsrf) {
			$session = $this->serviceContainer->get(Session::class);
			$csrfTokenStore = new SessionTokenStore(
				$session->getStore("webengine.csrf", true),
				$this->config->getInt("security.csrf_max_tokens")
			);
			$csrfTokenStore->setTokenLength(
				$this->config->getInt("security.csrf_token_length")
			);

			if($request->getMethod() === "POST") {
				$csrfTokenStore->verify($_POST);
			}

			$sharing = match($this->config->getString("security.csrf_token_sharing")) {
				"per-page" => HTMLDocumentProtector::ONE_TOKEN_PER_PAGE,
				default => HTMLDocumentProtector::ONE_TOKEN_PER_FORM,
			};
			$protector = new HTMLDocumentProtector(
				$this->viewModel,
				$csrfTokenStore
			);
			$tokens = $protector->protect($sharing);
			$this->response = $this->response->withHeader($this->config->getString("security.csrf_header"), $tokens);
		}
	}

	protected function handleProtectedGlobals():void {
		Protection::overrideInternals(
			Protection::removeGlobals($GLOBALS, [
					"_ENV" => explode(",", $this->config->getString("app.globals_whitelist_env") ?? ""),
					"_SERVER" => explode(",", $this->config->getString("app.globals_whitelist_server") ?? ""),
					"_GET" => explode(",", $this->config->getString("app.globals_whitelist_get") ?? ""),
					"_POST" => explode(",", $this->config->getString("app.globals_whitelist_post") ?? ""),
					"_FILES" => explode(",", $this->config->getString("app.globals_whitelist_files") ?? ""),
					"_COOKIES" => explode(",", $this->config->getString("app.globals_whitelist_cookies") ?? ""),
				]
			));
	}

	protected function handleLogicExecution():void {
		$logicExecutor = new LogicExecutor(
			$this->logicAssembly,
			$this->injector,
			$this->config->getString("app.namespace")
		);

		$fileFunc = "";
		ob_start(function(string $buffer)use(&$fileFunc) {
			if(!$buffer) {
				return;
			}
			call_user_func($this->obCallback, $fileFunc, $buffer);
		});

		foreach($logicExecutor->invoke("go_before") as $fileFunc) {
			ob_flush();
		}

		$input = $this->serviceContainer->get(Input::class);
		$input->when("do")->call(
			function(InputData $data)use($logicExecutor, &$fileFunc):void {
				$doString = "do_" . str_replace(
						"-",
						"_",
						$data->getString("do"),
					);
				foreach($logicExecutor->invoke($doString) as $fileFunc) {
					ob_flush();
				}
			}
		);

		foreach($logicExecutor->invoke("go") as $fileFunc) {
			ob_flush();
		}
		foreach($logicExecutor->invoke("go_after") as $fileFunc) {
			ob_flush();
		}
	}

	protected function setupLogger(ConfigSection $logConfig):void {
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

	protected function createRouter(Container $container):BaseRouter {
		$routerConfig = $this->config->getSection("router");
		$namespace = $this->config->getString("app.namespace");
		$appRouterFile = $routerConfig->getString("router_file");
		$appRouterClass = $routerConfig->getString("router_class");
		$defaultRouterFile = dirname(__DIR__, 2) . "/router.default.php";

		if(file_exists($appRouterFile)) {
			require_once($appRouterFile);
			$class = "\\$namespace\\$appRouterClass";
		}
		else {
			require_once($defaultRouterFile);
			$class = "\\Gt\\WebEngine\\DefaultRouter";
		}

		/** @var BaseRouter $router */
		$router = new $class($routerConfig);
		$router->setContainer($container);
		return $router;
	}

	private function setupResponse(ServerRequestInterface $request):void {
		$this->response = new Response(request: $request);

		$this->response->setExitCallback(fn() => call_user_func(
			$this->finishCallback,
			$this->response,
			$this->config->getSection("app")
		));
	}

	private function setupServiceContainer():void {
		if(isset($this->serviceContainer)) {
			return;
		}
		$this->serviceContainer = new Container();
		$this->serviceContainer->addLoaderClass(
			new DefaultServiceLoader(
				$this->config,
				$this->serviceContainer
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
					$this->serviceContainer,
				];
			}

			$this->serviceContainer->addLoaderClass(
				new $customServiceContainerClassName(
					...$constructorArgs
				)
			);
		}
	}

	/**
	 * Force trailing slashes in URLs. This is useful for consistency, but
	 * also helps identify that WebEngine requests do not match an actual
	 * static file, as file requests will never end in a slash. Another
	 * benefit is that links can behave relatively (e.g.
	 * <a href="./something/"> ) 307 is used here to preserve any POST data
	 * that may be in the request.
	 */
	private function forceTrailingSlashes(ServerRequestInterface $request):void {
		if(str_ends_with($request->getUri()->getPath(), "/")) {
			return;
		}

		$this->response = $this->response
			->withHeader(
				"Location",
				$request->getUri()->withPath(
					$request->getUri()->getPath() . "/"
				)
			)
			->withStatus(307);
	}
}
