<?php class LogTest extends PHPUnit_Framework_TestCase {
public function setUp() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Class/Log/Log.class.php");
	require_once(GTROOT . "/Class/Log/Logger.class.php");
	require_once(GTROOT . "/Framework/PageCode.php");
	require_once(GTROOT . "/Framework/EmptyObject.php");
}

public function tearDown() {
	removeTestApp();
}

public function testLoggerInstantiates() {
	Log::reset();
	$logger = Log::get();
	$this->assertInstanceOf("Logger", $logger);
}

public function testLoggerLogsDefault() {
	Log::reset();
	$logger = Log::get();
	$logger->info("Test!!!");

	$logFile = APPROOT . "/Default.log";

	$this->assertFileExists($logFile);
	$fileContents = file_get_contents($logFile);
	$this->assertContains("Test!!!", $fileContents);
}

public function testLoggerLogsManual() {
	Log::reset();
	$logger = Log::get("Nondefault");
	$logger->info("This log should go to Nondefault.log");
	$line = __LINE__ - 1;
	$file = __FILE__;

	$logFile = APPROOT . "/Nondefault.log";

	$this->assertFileExists($logFile);
	$fileContents = file_get_contents($logFile);
	$this->assertContains(" INFO [", $fileContents);
	$this->assertContains("$file :", $fileContents);
	$this->assertContains(":$line", $fileContents);
}

public function testLoggerConfig() {
	Log::reset();
	$cfgPhp = <<<PHP
<?php class Log_Config extends Config {
public static \$logLevel = "INFO";
public static \$path = "{REPLACE_WITH_CURRENT_DIR}";
public static \$datePattern = "d/m/Y H:i:s";
public static \$messageFormat = "%DATETIME% %LEVEL% Your log: %MESSAGE%\n";
}#
PHP;
	$cfgPhpPath = APPROOT . "/Config/Log_Config.cfg.php";
	$cfgPhp = str_replace("{REPLACE_WITH_CURRENT_DIR}", dirname(__DIR__),
		$cfgPhp);

	if(!is_dir(dirname($cfgPhpPath))) {
		mkdir(dirname($cfgPhpPath), 0775, true);
	}

	// Log file is in non-default place due to config override.
	$logPath = dirname(__DIR__) . "/Default.log";
	if(file_exists($logPath)) {
		unlink($logPath);
	}

	file_put_contents($cfgPhpPath, $cfgPhp);
	$logger = Log::get();
	$logger->info("This log should use the config settings.");
	$logger->trace("This line should not make it into the log.");

	// Ensure config file has been created successfully.
	$this->assertFileExists($cfgPhpPath);
	$this->assertFileExists($logPath);
	$logContents = file_get_contents($logPath);
	$this->assertContains(
		"This log should use the config settings.", $logContents);
	// Due to overridden logLevel, trace logs should not be recorded.
	$this->assertNotContains(
		"This line should not make it into the log.", $logContents);

	unlink($logPath);
}

public function testLoggerClassWhiteList() {
	Log::reset();
	$config = array();

	if(class_exists("Log_Config")) {
		$config["classWhiteList"] = array("WhiteListTestOne_PageCode");
		$config["path"] = APPROOT;
	}
	else {
		$path = APPROOT;
		$cfgPhp = <<<PHP
<?php class Log_Config extends Config {
public static \$classWhiteList = array("WhiteListTestOne_PageCode");
}#
PHP;
		$cfgPhpPath = APPROOT . "/Config/Log_Config.cfg.php";

		if(!is_dir(dirname($cfgPhpPath))) {
			mkdir(dirname($cfgPhpPath), 0775, true);
		}

		file_put_contents($cfgPhpPath, $cfgPhp);
	}

	// Log file is in non-default place due to config override.
	$logPath = APPROOT . "/WhiteListTest.log";
	if(file_exists($logPath)) {
		unlink($logPath);
	}


	$logger = Log::get("WhiteListTest", $config);

	$pageCode1 = $this->createPageCode("WhiteListTestOne");
	$pageCode1->go(null, null, null, null);

	$pageCode2 = $this->createPageCode("WhiteListTestTwo");
	$pageCode2->go(null, null, null, null);

	$logContents = file_get_contents($logPath);
	$this->assertContains("Log message from WhiteListTestOne", $logContents);
	$this->assertNotContains("Log message from WhiteListTestTwo", $logContents);
}

public function testLoggerClassBlackList() {
	Log::reset();
	$config = array();

	if(class_exists("Log_Config")) {
		$config["classWhiteList"] = array();
		$config["classBlackList"] = array("BlackListTestOne_PageCode");
	}
	else {
		$path = APPROOT;
		$cfgPhp = <<<PHP
<?php class Log_Config extends Config {
public static \$classBlackList = array("BlackListTestOne_PageCode");
}#
PHP;
		$cfgPhpPath = APPROOT . "/Config/Log_Config.cfg.php";

		if(!is_dir(dirname($cfgPhpPath))) {
			mkdir(dirname($cfgPhpPath), 0775, true);
		}
		
		file_put_contents($cfgPhpPath, $cfgPhp);
	}

	// Log file is in non-default place due to config override.
	$logPath = APPROOT . "/BlackListTest.log";
	if(file_exists($logPath)) {
		unlink($logPath);
	}


	$logger = Log::get("BlackListTest", $config);

	$pageCode1 = $this->createPageCode("BlackListTestOne", "Black");
	$pageCode1->go(null, null, null, null);

	$pageCode2 = $this->createPageCode("BlackListTestTwo", "Black");
	$pageCode2->go(null, null, null, null);

	$logContents = file_get_contents($logPath);
	$this->assertNotContains("Log message from BlackListTestOne", $logContents);
	$this->assertContains("Log message from BlackListTestTwo", $logContents);
}

private function createPageCode($name, $colour = "White") {
	$pcClassName = "{$name}_PageCode";
	$logName = $colour . "ListTest";
	$pcString = <<<PHP
<?php class $pcClassName extends PageCode {
public function go(\$api, \$dom, \$template, \$tool) {
	\$logger = Log::get("$logName");
	\$logger->info("Log message from $name");
}
}#
PHP;
	$pcDir = APPROOT . "/PageCode";
	$pcFilePath = "$pcDir/$name.php";

	if(!is_dir($pcDir)) {
		mkdir($pcDir, 0775, true);
	}
	file_put_contents($pcFilePath, $pcString);
	require_once($pcFilePath);

	return new $pcClassName(new EmptyObject());
}

}#