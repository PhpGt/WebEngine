<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

use Psr\Log\LogLevel;

class Logger_Test extends \PHPUnit_Framework_TestCase {

private $tmp;

private $logLevels = [
	LogLevel::EMERGENCY => 0,
	LogLevel::ALERT     => 1,
	LogLevel::CRITICAL  => 2,
	LogLevel::ERROR     => 3,
	LogLevel::WARNING   => 4,
	LogLevel::NOTICE    => 5,
	LogLevel::INFO      => 6,
	LogLevel::DEBUG     => 7,
];

public function data_logLevels() {
	$return = [];

	foreach ($this->logLevels as $logLevelDesired => $i) {
		foreach ($this->logLevels as $logLevelCurrent => $j) {
			$return []= [$logLevelDesired, $logLevelCurrent];
		}
	}

	return $return;
}

public function setUp() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();
	$_SERVER["DOCUMENT_ROOT"] = $this->tmp . "/www";
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

public function testLoggerConstructs() {
	$logger = new Logger();
	$this->assertInstanceOf(
		"\Katzgrau\KLogger\Logger",
		$logger,
		"Catch changes to implementation"
	);
}

/**
 * @dataProvider data_logLevels
 */
public function testLoggerLogs($logLevelDesired, $logLevelCurrent) {
	$logger = new Logger(null, $logLevelDesired);

	$uuid = uniqid();

	// PSR interface defines LogLevel constants.
	$logger->$logLevelCurrent(
		"Log being made with UUID $uuid - "
		. "A simple log message: current log level $logLevelCurrent, "
		. "made in desired log level $logLevelDesired");

	$logContents = "";
	// Current log level is within threshold.
	foreach(glob($this->tmp . "/log_*.txt") as $logFile) {
		$logContents .= file_get_contents($logFile);
	}

	if($this->logLevels[$logLevelDesired]
	>= $this->logLevels[$logLevelCurrent]) {
		$this->assertContains("UUID $uuid", $logContents);
	}
	else {
		// Current log level is NOT within threshold. Should be ignored.
		$this->assertNotContains("UUID $uuid", $logContents);
	}
}

}#