<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;
use \Gt\Core\Path;

class TestRunner {

const TYPE_ALL = "type_all";
const TYPE_UNIT = "type_unit";
const TYPE_INTEGRATION = "type_integration";

const ERRORLEVEL_UNIT = 1;
const ERRORLEVEL_INTEGRATION = 2;

private $approot;
private $type;

private $countUnit = 0;
private $countIntegration = 0;

private $descriptorSpec = [
	0 => ["pipe", "r"],
	1 => ["pipe", "r"],
	2 => ["pipe", "r"],
];

public function __construct($approot, $type) {
	$type = strtolower("TYPE_" . $type);

	$this->approot = $approot;
	$this->type = $type;

	$unitResult = 0;
	$integrationResult = 0;

	switch ($type) {
	case self::TYPE_ALL:
		$unitResult = $this->testUnit();
		$integrationResult = $this->testIntegration();
		break;

	case self::TYPE_UNIT:
		$unitResult = $this->testUnit();
		break;

	case self::TYPE_INTEGRATION:
		$integrationResult = $this->testIntegration();
		break;

	default:
		throw new InvalidTestRunnerTypeException($type);
		break;
	}

	if($this->countIntegration > 0) {
		echo "\nIntegration tests completed.";
	}
	else {
		echo "\nNo integration tests have been run.";
	}
	if($this->countUnit > 0) {
		echo "\nUnit tests completed.";
	}
	else {
		echo "\nNo unit tests have been run.";
	}

	echo "\n";

	$errorLevel = $unitResult | $integrationResult;

	if($errorLevel == 0) {
		if($this->countUnit + $this->countIntegration === 0) {
			echo "\n - NO TESTS : No tests have been detected.";
		}
		else {
			if($this->countUnit > 0) {
				echo "\n - SUCCESS : Unit testst passing!";
			}
			if($this->countIntegration > 0) {
				echo "\n ✓ SUCCESS : Integration tests passing!";
			}			
		}
	}
	else {
		echo "\n ✗ FAILURE : ";

		if($errorLevel & self::ERRORLEVEL_UNIT
		&& $errorLevel & self::ERRORLEVEL_INTEGRATION) {
			echo "Both unit and integration";

		}
		else if($errorLevel & self::ERRORLEVEL_UNIT) {
			echo "Integration tests passed, but unit";
		}
		else if($errorLevel & self::ERRORLEVEL_INTEGRATION) {
			echo "Unit tests passed, but integration";
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
	$result = 0;
	$rememberCwd = getcwd();

	$testPath = Path::fixCase(getcwd() . "/test/unit");

	if(!is_dir($testPath)) {
		return 0;
	}

	$testExec = realpath("./vendor/bin/phpunit");
	chdir($testPath);
	passthru($testExec, $result);
 
	if($result === 0) {
		$this->countUnit++;
	}
	else {
		$result = self::ERRORLEVEL_UNIT;
	}

	// Reset the cwd.
	chdir($rememberCwd);
	return $result;
}

/**
 *
 */
private function testIntegration() {
	$result = 0;
	$rememberCwd = getcwd();

	$testPath = Path::fixCase(getcwd() . "/test/integration");

	if(!is_dir($testPath)) {
		return 0;
	}

	$serverCommand = "./vendor/bin/serve";
	$server = proc_open($serverCommand, $this->descriptorSpec, $pipes);

	$testExec = realpath("./vendor/bin/behat");
	chdir($testPath);
	passthru($testExec, $result);

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

	if($result === 0) {
		$this->countIntegration++;
	}
	else {
		$result = self::ERRORLEVEL_INTEGRATION;
	}

	// Reset the cwd.
	chdir($rememberCwd);
	return $result;
}

}#
