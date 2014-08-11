<?php
/**
 * Acts as the entry point for processing PHP.Gt applications. Could be referred
 * to as the PHP.Gt bootstrapper. Only intended for serving dynamic responses.
 * The webserver should be set up to handle serving static files.
 * When using PHP.Gt's inbuilt server (gtserver), Gateway.php serves static
 * files without instantiating this class.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

use \Gt\Request\Standardiser;
use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Response\Redirect;
use \Gt\Api\ApiFactory;
use \Gt\Database\DatabaseFactory;
use \Gt\Dispatcher\DispatcherFactory;

final class Start {

public function __construct($uri) {
	if(empty($_SERVER)) {
		throw new \Gt\Core\Exception\UndefinedVariableException(
			"\$_SERVER is not defined. Are you running from cli?");
	}

	$config = new Config();
	$production = $config["app"]->production;

	$standardiser = new Standardiser();
	$uriFixed = $standardiser->fixUri($uri, $config["request"]);
	$this->redirect($uri, $uriFixed, $production);

	$request  = new Request ($uri, $config["request"]);
	$response = new Response($config["response"]);

	$apiFactory = new ApiFactory($config["api"]);
	$dbFactory  = new DatabaseFactory($config["database"]);

	$dispatcher = DispatcherFactory::createDispatcher(
		$request,
		$response,
		$apiFactory,
		$dbFactory
	);

	// Dispatcher::process returns null on a successful call, only returning
	// a string when a redirect is required.
	$this->redirect($uri, $dispatcher->process(), $production);
}

/**
 * @param string $uri1 Originally requested URI.
 * @param string $uri2 Fixed or changed URI.
 *
 * @return Redirect Object representing the redirection.
 */
private function redirect($uri1, $uri2, $production) {
	if(is_null($uri1) || is_null($uri2)) {
		return;
	}

	if(strcmp($uri1, $uri2) !== 0) {
		// Only perform permanent redirects on production applications.
		$code = 302;
		if($production) {
			$code = 301;
		}

		return new Redirect($uri2, $code);
	}
}

}#