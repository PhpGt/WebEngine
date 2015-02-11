<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class Config_Test extends \PHPUnit_Framework_TestCase {

private $root;
private $configPath;

public function setUp() {
	$this->root = sys_get_temp_dir() . "/gt-test";
	$this->configPath = $this->root . "/config.ini";

	$_SERVER["DOCUMENT_ROOT"] = $this->root . "/www";

	if(!is_dir($this->root)) {
		mkdir($this->root, 0777, true);
	}
}

public function tearDown() {
	if(is_file($this->configPath)) {
		unlink($this->configPath);
	}
	if(is_dir($this->root)) {
		rmdir($this->root);
	}
}

public function testConfigFileNotRequired() {
	$this->tearDown();
	// Make sure an exception is not thrown now Configs are optional.
	new Config();
}

public function testLoadsMultipleVariables() {
	$cfgString = <<<CFG
[database]
driver = "MySQL"
host = "localhost"
username = "dbuser"
password = "dbpass"
; comment to confuse things
dbname = "my-db"
; another comment to confuse things

[app]
production = false
client_compiled = true
timezone = "UTC"

[security]
adminIP = "localhost"
salt = "salt chilli beef"
adminIP = "127.0.0.1"
CFG;
	file_put_contents($this->configPath, $cfgString);

	$config = new Config();

	// Test a few values are stored in their correct type:
	$this->assertEquals("MySQL", $config["database"]->driver);
	$this->assertEquals("my-db", $config["database"]->dbname);

	$this->assertTrue(false == $config["app"]->production);
	$this->assertTrue(true == $config["app"]->client_compiled);

	$this->assertEquals("127.0.0.1", $config["security"]->adminIP);

	$this->assertTrue(isset($config["security"]));
}

public function testDefaultConfigMerges() {
	$cfgString = <<<CFG
[request]
force_extension = true

[database]
driver = "MySQL"
dbname = "my-db"

[app]

[security]
adminIP = "127.0.0.1"
CFG;
	file_put_contents($this->configPath, $cfgString);

	$configDefault = [
		"request" => [
			"force_extension" => false,
			"pageview_trailing_slash" => false,
		],
		"database" => [
			"driver" => "mysql",
			"host" => "localhost",
		],
		"app" => [
			"production" => false,
			"client_compiled" => false,
			"timezone" => "UTC",
		],
	];
	$config = new Config($configDefault);

	$this->assertTrue(true == $config["request"]->force_extension);
	$this->assertTrue(false == $config["request"]->pageview_trailing_slash);
	$this->assertNotEquals("mysql", $config["database"]->driver);
	$this->assertTrue(false == $config["app"]->production);
}

public function testCaseInsensitive() {
	$cfgString = <<<CFG
[request]
force_extension = true

[DataBase]
driver = "MySQL"
dbname = "my-db"

[App]
namespace = TestApp
CFG;
	file_put_contents($this->configPath, $cfgString);

	$configDefault = [
		"request" => [
			"force_extension" => false,
			"pageview_trailing_slash" => false,
		],
		"database" => [
			"driver" => "mysql",
			"host" => "localhost",
		],
		"app" => [
			"production" => false,
			"client_compiled" => false,
			"timezone" => "UTC",
		],
	];
	$config = new Config($configDefault);

	$this->assertTrue(true == $config["request"]->force_extension);
	$this->assertTrue(false == $config["request"]->pageview_trailing_slash);
	$this->assertTrue(false == $config["app"]->production);
	$this->assertEquals("TestApp", $config["app"]->namespace);
}

}#