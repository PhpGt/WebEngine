<?php
/**
 * Executes a system command and arguments in a separate process
 * and writes back to STDOUT.
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
class Process {

private $fp;
private $processString;

public function __construct($processName, array $processArgs) {
	$this->processString = $processName;

	foreach ($processArgs as $key => $value) {
		$this->processString .= " ";
		if(!empty($key)) {
			$this->processString .= "-$key=";
		}
		$this->processString .= $value;
	}

	
}

public function __destruct() {
	pclose($this->fp);
}

public function run() {
	$this->fp = popen(
		$this->processString
		// Redirect STDOUT to this process's STDIN.
		. " 1>&0",
		"r"
	);

	if(!is_resource($this->fp)) {
		throw new ProcessFailedException();
	}

	// Pass back all output from newly-spawned process.
	while(false !== ($s = fread($this->fp, 1024)) ) {
		fwrite(STDOUT, $s);
	}
}

}#