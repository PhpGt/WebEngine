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
use \Gt\Request\Standardiser as Standardiser;

final class Go {

public function __construct($uri) {
	if(empty($_SERVER)) {
		throw new \Gt\Core\Exception\UndefinedVariableException(
			"\$_SERVER is not defined. Are you running from cli?");
	}

	$config = new Config();
	$uriNorm = Standardiser::apply($config["request"], $uri);
	
	// var_dump($_SERVER["HTTP_ACCEPT"]);die("!!!");
}

}#