<?php
namespace Gt\WebEngine\Dispatch;

use Gt\Http\Stream;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\View;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\FileSystem\BasenameNotFoundException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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
			die("The requested view is not found!!!");
		}

		$logicAssembly = $this->router->getLogicAssembly($path);

		foreach($logicAssembly as $logicPath) {
			$class = $this->getLogicClassFromFilePath($logicPath);
			var_dump($class);die("This is the logic class");
		}
		die("EOF");

		$stream = new Stream("php://memory");
		$response = $response->withBody($stream);

//		$this->streamResponse()

//		try {
//			$existingViews = $viewAssembly->getExistingItems();
//		}
//		catch(EmptyAssemblyException $exception) {
//			// TODO: There may be a _dynamic.php file to take over.
//			$foundDynamicPhp = false;
//			if(!$foundDynamicPhp) {
//				throw new NotFoundException();
//			}
//		}
	}

	protected abstract function getViewModel(string $body):View;
	protected abstract function getLogicClassFromFilePath(string $logicPath):string;

	protected function streamResponse(string $viewFile, StreamInterface $body) {
		$bodyContent = file_get_contents($viewFile);
		$body->write($bodyContent);
	}
}