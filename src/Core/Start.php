<?php
/**
 * Acts as the entry point for processing all applications. Could be referred
 * to as the bootstrapper. Only intended for serving dynamic responses.
 * The webserver should be set up to handle serving static files.
 * When using the inbuilt server, Gateway.php serves static files without
 * instantiating this class.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

use \Gt\Request\Standardiser;
use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Response\Redirect;
use \Gt\Api\Api;
use \Gt\Dispatcher\DispatcherFactory;
use \Gt\Session\Session;
use \Gt\Data\Data;

final class Start {

public function __construct($uri) {
	if(empty($_SERVER)) {
		throw new \Gt\Core\Exception\UndefinedVariableException(
			"\$_SERVER is not defined. Are you running from cli?");
	}

	$config = new Config();

	$this->setPathConfig($config);

	$appNamespace = $config["app"]->namespace;
	if(empty($appNamespace)) {
		throw new \Gt\Core\Exception\RequiredAppResourceNotFoundException(
			"No App namespace defined. Are you sure you have a config.ini?");
	}

	define("APP_NAMESPACE", $appNamespace);

	$this->addAppAutoloader(APP_NAMESPACE);

	$production = $config["app"]->production;
	$this->setupEnvironment($config);

	$standardiser = new Standardiser();
	$uriFixed = $standardiser->fixUri($uri, $config["request"]);
	$this->redirect($uri, $uriFixed, $production);

	$request  = new Request ($uri, $config["request"]);
	$response = new Response($config["response"], $production);

	$sessionNs = $config["app"]->namespace;
	if($config["session"]->base_namespace != false) {
		$sessionNs = $config["session"]->base_namespace;
	}
	$session = new Session($config["session"], $sessionNs);
	$api = new Api($config["api"], $response->content, $session);
	$data = new Data($config["data"]);

	$dispatcher = DispatcherFactory::createDispatcher(
		$appNamespace,
		$request,
		$response,
		$api,
		$session,
		$data
	);

	// Dispatcher::process returns null on a successful call, only returning
	// a string when a redirect is required.
	$this->redirect($uri, $dispatcher->process(), $production);
}

/**
 * Adds the application's class path to Composer's autoloader.
 *
 * @param string $appNamespace Base namespace containing all application logic
 */
private function addAppAutoloader($appNamespace) {
	$autoloadPath = realpath(Path::get(Path::GTROOT) . "/../../autoload.php");
	if(false === $autoloadPath) {
		$autoloadPath = realpath(Path::get(Path::GTROOT)
			. "/vendor/autoload.php");
	}
	$loader = require $autoloadPath;

	$classDir = Path::fixCase(implode("/", [
		Path::get(Path::SRC),
		"Class",
	]));

	if(is_dir($classDir)) {
		$loader->addPsr4($appNamespace . "\\", $classDir);
	}

	$loader->addPsr4($appNamespace . "\\", Path::get(Path::SRC));
}

/**
 * @param string $uri1 Originally requested URI
 * @param string $uri2 Fixed or changed URI
 *
 * @return Redirect|null Object representing the redirection, or null if either
 * uri is provided as null
 */
private function redirect($uri1, $uri2, $production) {
	if(is_null($uri1) || is_null($uri2)) {
		return null;
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

/**
 * Passes the Path class the required configuration objects.
 *
 * @param Config $config The Config object - not all ConfigObj objects contained
 * are required by path; only pass the required objects.
 */
public function setPathConfig($config) {
	Path::setConfig($config["api"]);
}

/**
 * Simple bootstrapping function to set PHP environment according to
 * configuration options.
 *
 * @param Config $config This application's Configuration object
 */
private function setupEnvironment($config) {
	ini_set("default_charset", $config["app"]->encoding);
	mb_internal_encoding($config["app"]->encoding);
}

}#