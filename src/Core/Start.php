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

	$standardiser = new Standardiser();
	$uriFixed = $standardiser->fixUri($uri, $config["request"]);
	if($uri !== $uriFixed) {
		// Only perform permanent redirects on production applications.
		$code = 302;
		if($config["app"]->production) {
			$code = 301;
		}

		var_dump($uriFixed);die("!!!!!!");
		return new Redirect($uriFixed, $code);
	}

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

	$dispatcher->process();
}

}#