<?php
/**
 * Executes a system command and arguments in a separate process
 * and writes back to specified output stream.
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
class Process {

private $fp;

public function __construct($processName, array $processArgs) {
	$processArgs = implode(" ", $processArgs);

}

public function __destruct() {
	pclose($fp);
}

public function run($stream = STDOUT) {
	$this->fp = popen(
		$this->processName
		. " "
		. $this->processArgs
		. " "
		// Redirect STDOUT to this process's STDIN.
		. "1>&0",

		"r"
	);

	$fp = popen(
		"php -S localhost:{$this->port} "
		. "-t {$this->approot}/www "
		. "{$this->gtroot}/Core/Router.php "
		
		. "1>&0", 
		"r"
	);

	if(!is_resource($this->fp)) {
		throw new Gt\Exception\
	}

	// Pass back all output from newly-spawned process.
	while(false !== ($s = fread($this->fp, 1024)) ) {
		fwrite($stream, $s);
	}
}

}#