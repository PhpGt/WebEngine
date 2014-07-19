<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
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
	unlink($this->configPath);
	rmdir($this->root);
}

// public function testLoadsFile() {
// 	$this->assertTrue(touch($this->configPath), "Create empty config file");

// 	$config = new Config();
// 	$vars = get_object_vars($config);

// 	$this->assertEmpty($vars);

// 	file_put_contents($this->configPath, "var = test");
// 	$config = new Config();
// 	$vars = get_object_vars($config);

// 	$this->assertNotEmpty($vars);
// }

// public function testLoadsVariable() {
// 	file_put_contents($this->configPath, "var = test");
	
// 	$config = new Config();
// 	$this->assertEquals("test", $config->var);
// }

public function testLoadsMultipleVariables() {
	$cfgString = <<<CFG
[database]
driver = "MySQL"
host = "localhost"
username = "dbuser"
password = "dbpass"
# comment to confuse things
dbname = "my-db"
# another comment to confuse things

[app]
production = false
clientcompiled = true
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
	$this->assertTrue(true == $config["app"]->clientcompiled);

	$this->assertEquals("127.0.0.1", $config["security"]->adminIP);

	$this->assertTrue(isset($config["security"]));
}

}#