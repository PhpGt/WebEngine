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

/**
 * @param string $processName The process to spawn
 * @param array $processArgs Associative array of arguments to pass
 */
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
	if(is_resource($this->fp)) {
		pclose($this->fp);
	}
}

public function run($dummyRun = false) {
	if($dummyRun) {
		return $this->processString;
	}

	$this->fp = popen(
		$this->processString
		// Redirect STDOUT to this process's STDIN.
		. " 1>&0",
		"r"
	);

	// Pass back all output from newly-spawned process.
	while(false !== ($s = fread($this->fp, 1024)) ) {
		fwrite(STDOUT, $s);
	}
}

}#