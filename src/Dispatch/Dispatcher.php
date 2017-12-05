<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\HttpError\NotFoundException;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\ViewFileNotFoundException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Dispatcher {
	/** @var Router */
	protected $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}
	public function handle(RequestInterface $request, ResponseInterface $response):void {
		$path = $request->getUri()->getPath();

		try {
			$this->streamResponse(
				$this->router->getViewFile($path),
				$response->getBody()
			);
		}
		catch(ViewFileNotFoundException $exception) {
			// TODO: There may be a _dynamic.php file to take over.
			$foundDynamicPhp = false;
			if(!$foundDynamicPhp) {
				throw new NotFoundException();
			}
		}
	}

	protected function streamResponse(string $viewFile, StreamInterface $body) {
		$bodyContent = file_get_contents($viewFile);
		$body->write($bodyContent);
	}
}