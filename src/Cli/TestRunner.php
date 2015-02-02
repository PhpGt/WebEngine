<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;
use \Gt\Core\Path;

class TestRunner {

const TYPE_ALL = "type_all";
const TYPE_UNIT = "type_unit";
const TYPE_ACCEPTANCE = "type_acceptance";

const ERRORLEVEL_UNIT = 1;
const ERRORLEVEL_ACCEPTANCE = 2;

private $approot;
private $type;

private $countUnit = 0;
private $countAcceptance = 0;

private $descriptorSpec = [
	0 => ["pipe", "r"],
	1 => ["pipe", "w"],
	2 => ["pipe", "w"],
];

public function __construct($approot, $type) {
	$type = strtolower("TYPE_" . $type);

	$this->approot = $approot;
	$this->type = $type;

	$unitResult = 0;
	$acceptanceResult = 0;

	switch ($type) {
	case self::TYPE_ALL:
		$unitResult = $this->testUnit();
		$acceptanceResult = $this->testAcceptance();
		break;

	case self::TYPE_UNIT:
		$unitResult = $this->testUnit();
		break;

	case self::TYPE_ACCEPTANCE:
		$acceptanceResult = $this->testAcceptance();
		break;

	default:
		throw new InvalidTestRunnerTypeException($type);
		break;
	}

	if($this->countAcceptance > 0) {
		echo "\nAcceptance tests completed.";
	}
	else {
		echo "\nNo acceptance tests have been run.";
	}
	if($this->countUnit > 0) {
		echo "\nUnit tests completed.";
	}
	else {
		echo "\nNo unit tests have been run.";
	}

	echo "\n";

	$errorLevel = $unitResult | $acceptanceResult;

	if($errorLevel == 0) {
		if($this->countUnit + $this->countAcceptance === 0) {
			echo "\n - NO TESTS : No tests have been detected.";
		}
		else {
			echo "\n ✓ SUCCESS : All tests passing.";
		}
	}
	else {
		echo "\n ✗ FAILURE : ";

		if($errorLevel & self::ERRORLEVEL_UNIT
		&& $errorLevel & self::ERRORLEVEL_ACCEPTANCE) {
			echo "Both unit and acceptance";

		}
		else if($errorLevel & self::ERRORLEVEL_UNIT) {
			echo "Acceptance tests passed, but unit";
		}
		else if($errorLevel & self::ERRORLEVEL_ACCEPTANCE) {
			echo "Unit tests passed, but acceptance";
		}

		echo " tests failed.";
	}

	echo "\n\n";
	exit($errorLevel);
}

/**
 *
 */
private function testUnit() {

}

/**
 *
 */
private function testAcceptance() {
	$result = 0;
	$baseCwd = getcwd();
	$gtroot = Path::get(Path::GTROOT);
	$root = Path::get(Path::ROOT);
	$testPath = Path::fixCase($this->approot . "/test/Acceptance");

	if(!is_dir($testPath)) {
		return 0;
	}

	$testWebroot = "$testPath/www";
	if(!is_dir($testWebroot)) {
		mkdir($testWebroot, 0775, true);
	}


	$serverCommand = "$gtroot/bin/serve --approot=$root --port=8089";
	$server = proc_open($serverCommand, $this->descriptorSpec, $pipes);

	chdir($testPath);
	$test = system("$gtroot/vendor/bin/behat", $exitCode);

	// Inbuilt server spawns child processes that need killing.
	$status = proc_get_status($server);
	$pid = $status["pid"];
	$pidArray = [];

	while(!empty($pid)) {
		$pidArray []= $pid;
		$pid = exec("pgrep -P $pid");
	}

	proc_terminate($server);
	proc_close($server);
	foreach ($pidArray as $p) {
		posix_kill($p, SIGKILL);
	}

	if($exitCode == 0) {
		$this->countAcceptance++;
	}
	else {
		$result = self::ERRORLEVEL_ACCEPTANCE;
	}

	// Reset the cwd.
	chdir($baseCwd);
	return $result;
}

}#