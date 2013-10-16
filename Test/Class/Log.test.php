<?php class SessionTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	createTestApp();
	require_once(GTROOT . "/Class/Log/Log.class.php");
	require_once(GTROOT . "/Class/Log/Logger.class.php");
}

public function tearDown() {
	removeTestApp();
}

public function testLoggerInstantiates() {
	$logger = Log::get();
	$this->assertInstanceOf("Logger", $logger);
}

public function testLoggerLogsDefault() {
	$logger = Log::get();
	$logger->info("Test!!!");

	$logFile = APPROOT . "/Default.log";

	$this->assertFileExists($logFile);
	$fileContents = file_get_contents($logFile);
	$this->assertContains("Test!!!", $fileContents);
}

public function testLoggerLogsManual() {
	$logger = Log::get("Nondefault");
	$logger->info("This log should go to Nondefault.log");
	$line = __LINE__ - 1;
	$file = __FILE__;

	$logFile = APPROOT . "/Nondefault.log";

	$this->assertFileExists($logFile);
	$fileContents = file_get_contents($logFile);
	$this->assertContains(" INFO [", $fileContents);
	$this->assertContains("[$file :", $fileContents);
	$this->assertContains(":$line", $fileContents);
}

}#