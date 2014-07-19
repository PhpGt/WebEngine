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

public function testLoadsFile() {
	$this->assertTrue(touch($this->configPath), "Create empty config file");

	$config = new Config();
	$vars = get_object_vars($config);

	$this->assertEmpty($vars);

	file_put_contents($this->configPath, "var = test");
	$config = new Config();
	$vars = get_object_vars($config);

	$this->assertNotEmpty($vars);
}

}#