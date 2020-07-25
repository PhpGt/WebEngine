<?php
namespace Gt\WebEngine\Route;

use Negotiation\Accept;
use Negotiation\Negotiator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RouterFactory {
	const ACCEPT_PRIORITIES = [
		"text/html; charset=UTF-8",
		"application/json",
		"application/xml;q=0.5",
	];
	const TYPE_MAP = [
		"text/html" => PageRouter::class,
		"application/json" => ApiRouter::class,
		"application/xml" => ApiRouter::class,
	];
	private string $defaultContentType;

	public function __construct(string $defaultContentType = "text/html") {
		$this->defaultContentType = $defaultContentType;
	}

	public function create(
		RequestInterface $request,
		string $documentRoot
	):Router {
		$typeHeader = $request->getHeaderLine("accept");
		$type = $this->getType($typeHeader);
		$routerClass = $this->getRouterClassForType($type);

		/** @var Router $router */
		$router = new $routerClass(
			$request,
			$documentRoot,
			$type
		);

		return $router;
	}

	protected function getType(string $accept = null):string {
		if(empty($accept)) {
			$accept = $this->defaultContentType;
		}
		
		$negotiator = new Negotiator();

		$priorities = self::ACCEPT_PRIORITIES;
		array_unshift($priorities, $this->defaultContentType);

		/** @var Accept $acceptHeader */
		$acceptHeader = $negotiator->getBest(
			$accept,
			$priorities
		);

		$type = null;
		if($acceptHeader) {
			$type = $acceptHeader->getType();
		}

		if(empty($type)) {
			throw new RoutingException("Accept header has no route: $accept");
		}

		return $type;
	}

	protected function getRouterClassForType(string $type):string {
		if(empty($type)) {
			$type = $this->defaultContentType;
		}

		foreach(explode(",", $type) as $singleType) {
			$singleType = strtok($singleType, ";");
			if(array_key_exists($singleType, self::TYPE_MAP)) {
				return self::TYPE_MAP[$singleType];
			}
		}
	}
}
