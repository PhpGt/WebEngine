<?php
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

class FeatureContext extends MinkContext {
	/** @var \Gt\Daemon\Process */
	private static $process;
	private static $pid;

	/** @BeforeSuite */
	public static function setUp(BeforeSuiteScope $event) {
		$cwd = getcwd();
		chdir(__DIR__ . "/../../../..");
		$serveCommand = "vendor/bin/serve";
		self::$process = new Gt\Daemon\Process("$serveCommand");
		self::$process->exec();
		self::$pid = self::$process->getPid();
		posix_setpgid(self::$pid, self::$pid);
		chdir($cwd);
		sleep(1);
	}

	/** @AfterSuite */
	public static function tearDown(AfterSuiteScope $event) {
		posix_kill(-self::$pid, 2);
		self::$process->terminate(2);
	}
}