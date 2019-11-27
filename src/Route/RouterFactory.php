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
	const TYPE_DEFAULT = "text/html";

	public function create(
		RequestInterface $request,
		string $documentRoot
	):Router {
		$typeHeader = $request->getHeaderLine("accept");
		$type = $this->getType($typeHeader);
		$routerClass = $this->getRouterClassForType($typeHeader);

		/** @var Router $router */
		$router = new $routerClass(
			$request,
			$documentRoot,
			$type
		);

		return $router;
	}

	protected function getType(string $accept = null):?string {
		if(empty($accept)) {
			$accept = self::TYPE_DEFAULT;
		}
		
		$negotiator = new Negotiator();
		/** @var Accept $acceptHeader */
		$acceptHeader = $negotiator->getBest(
			$accept,
			self::ACCEPT_PRIORITIES
		);

		$type = null;
		if($acceptHeader) {
			$type = $acceptHeader->getType();
		}

		if(empty($type)) {
			$type = self::TYPE_DEFAULT;
		}

		return $type;
	}

	protected function getRouterClassForType(string $type):string {
		if(empty($type)) {
			$type = self::TYPE_DEFAULT;
		}

		foreach(explode(",", $type) as $singleType) {
			$singleType = strtok($singleType, ";");
			if(array_key_exists($singleType, self::TYPE_MAP)) {
				return self::TYPE_MAP[$singleType];
			}
		}

		throw new RoutingException("Accept header has no route: $type");
	}
}
