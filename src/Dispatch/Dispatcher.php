<?php
namespace Gt\WebEngine\Dispatch;

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

	public function handle(RequestInterface $request, ResponseInterface $response):void {
		$path = $request->getUri()->getPath();

		try {
			$viewAssembly = $this->router->getViewAssembly($path);
			$viewModel = $this->getViewModel((string)$viewAssembly);
		}
		catch(BasenameNotFoundException $exception) {
// TODO: Handle view not found.
			die("The requested view is not found!!!");
		}

		$baseLogicDirectory = $this->router->getBaseViewLogicPath();

		$logicAssembly = $this->router->getLogicAssembly($path);
		$logicObjects = [];

// TODO: Pass the LogicFactory default values to use when creating logics, e.g. viewmodel, database, etc.
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

// TODO: Execute the logic objects!
	}

	protected abstract function getViewModel(string $body):View;
	protected abstract function getBaseLogicDirectory(string $docRoot):string;

	protected function streamResponse(string $viewFile, StreamInterface $body) {
		$bodyContent = file_get_contents($viewFile);
		$body->write($bodyContent);
	}
}