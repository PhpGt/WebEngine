<?php
/**
 * Executes a system command and arguments in a separate process
 * and writes back to STDOUT.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
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

/**
 * Spawns a new process and writes the process's output to STDOUT.
 *
 * @param bool $dummyRun Defaults to false. Set to true to skip opening of the
 * new process and instead just return the process string
 *
 * @return string The exact string executed on the system
 */
public function run($dummyRun = false) {
	if(!$dummyRun) {
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

	return $this->processString;
}

}#