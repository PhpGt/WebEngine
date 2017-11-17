<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\Route\Router;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Dispatcher {
	/** @var Router */
	protected $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}
	public function handle(RequestInterface $request, ResponseInterface $response) {
		$path = $request->getUri()->getPath();
		$viewFile = $this->router->getViewFile($path);

		var_dump($viewFile);die();
	}
}