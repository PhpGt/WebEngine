<?php
/**
 * Used as a wrapper to the PHP built-in server to handle directory paths and
 * alert the developer if directories do not exist, before starting the server.
 *
 * This script should be executed from the base directory of the PHP.Gt
 * application wishing to be served (the "approot"), either by referencing the
 * script absolutely, or by having it within the user's environment path.
 * Alternatively, the base directory can be passed as the --approot argument.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
use \Symfony\Component\Console\Input\ArgvInput;

final class Server {

private $gtroot;
private $approot;
private $port;
private $process;

public function __construct(ArgvInput $arguments) {
	$this->gtroot = dirname(__DIR__);
	$this->approot = $arguments->getOption("approot");
	$this->port = $arguments->getOption("port");

	$this->process = new Process(
		"php", [
		"S" => "localhost:{$this->port}",
		"t" => "{$this->approot}/www",
		"{$this->gtroot}/Cli/Gatekeeper.php",
	]);
	$this->process->run();
}

}#