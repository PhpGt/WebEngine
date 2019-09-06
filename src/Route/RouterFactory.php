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

	protected static $routerClassLookup = [];

	public static function create(
		RequestInterface $request,
		string $documentRoot
	):Router {
		$type = self::getType($request->getHeaderLine("accept"));
		$routerClass = self::getRouterClassForType($type);

		/** @var Router $router */
		$router = new $routerClass(
			$request,
			$documentRoot,
			$type
		);

		return $router;
	}

	protected static function getType(string $accept = null):string {
		if(empty($accept)) {
			$accept = "text/html";
		}

		$negotiator = new Negotiator();
		/** @var Accept $acceptHeader */
		$acceptHeader = $negotiator->getBest(
			$accept,
			self::ACCEPT_PRIORITIES
		);
		$type = $acceptHeader->getType();
		if(empty($type)) {
			$type = self::TYPE_DEFAULT;
		}

		return $type;
	}

	protected static function getRouterClassForType(string $type):string {
		if(!array_key_exists($type, self::TYPE_MAP)) {
			throw new RoutingException("Accept header has no route: $type");
		}

		return self::TYPE_MAP[$type];
	}
}
