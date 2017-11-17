<?php
namespace Gt\WebEngine\Dispatch;

use Gt\WebEngine\Route\Router;
use Psr\Http\Message\RequestInterface;

class Dispatcher {
	/** @var Router */
	protected $router;

	public function __construct(Router $router) {
		$this->router = $router;
	}
	public function handle(RequestInterface $request) {
		$path = $request->getUri()->getPath();
		$baseViewLogicPath = $this->router->getBaseViewLogicPath();
		$viewLogicSubPath = $this->router->getViewLogicSubPath($path);
	}
}