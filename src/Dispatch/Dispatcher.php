<?php
namespace Gt\WebEngine\Dispatch;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\FileSystem\Assembly;
use Gt\WebEngine\Logic\LogicFactory;
use Gt\WebEngine\View\View;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\FileSystem\BasenameNotFoundException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use TypeError;

abstract class Dispatcher {
	/** @var Router */
	protected $router;
	protected $appNamespace;

	public function __construct(Router $router, string $appNamespace) {
		$this->router = $router;
		$this->appNamespace = $appNamespace;
	}

	public function storeInternalObjects(
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		Cookie $cookie,
		Session $session
	):void {
		LogicFactory::setConfig($config);
		LogicFactory::setServerInfo($serverInfo);
		LogicFactory::setInput($input);
		LogicFactory::setCookie($cookie);
		LogicFactory::setSession($session);
	}

	public function handle(RequestInterface $request, ResponseInterface $response):void {
		$path = $request->getUri()->getPath();

		try {
			$templateDirectory = implode(DIRECTORY_SEPARATOR, [
				$this->router->getBaseViewLogicPath(),
				"_template",
			]);
			$viewAssembly = $this->router->getViewAssembly($path);
			$view = $this->getView(
				$response->getBody(),
				(string)$viewAssembly,
				$templateDirectory
			);
		}
		catch(BasenameNotFoundException $exception) {
// TODO: Handle view not found.
			die("The requested view is not found!!!");
		}

		LogicFactory::setView($view);
		$baseLogicDirectory = $this->router->getBaseViewLogicPath();

		$logicAssembly = $this->router->getLogicAssembly($path);
		$logicObjects = $this->createLogicObjects(
			$logicAssembly,
			$baseLogicDirectory
		);

		$this->dispatchLogicObjects($logicObjects);
		$view->stream();
	}

	protected abstract function getView(
		StreamInterface $outputStream,
		string $body,
		string $templateDirectory
	):View;
	protected abstract function getBaseLogicDirectory(string $docRoot):string;

	protected function streamResponse(string $viewFile, StreamInterface $body) {
		$bodyContent = file_get_contents($viewFile);
		$body->write($bodyContent);
	}

	protected function createLogicObjects(
		Assembly $logicAssembly,
		string $baseLogicDirectory
	):array {
		$logicObjects = [];

		foreach($logicAssembly as $logicPath) {
			try {
				$logicObjects []= LogicFactory::createPageLogicFromPath(
					$logicPath,
					$this->appNamespace,
					$baseLogicDirectory
				);
			}
			catch(TypeError $exception) {
				throw new IncorrectLogicObjectType($logicPath);
			}
		}

		return $logicObjects;
	}

	protected function dispatchLogicObjects(array $logicObjects):void {
		foreach($logicObjects as $logic) {
			$logic->go();
		}
	}
}