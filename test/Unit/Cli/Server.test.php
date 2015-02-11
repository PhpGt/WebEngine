<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;

class Server_Test extends \PHPUnit_Framework_TestCase {

private $arguments;
private $approot;

public function setUp() {
	$this->approot = \Gt\Test\Helper::createTmpDir();

	$this->arguments = $this->getMock(
		"\Symfony\Component\Console\Input\ArgvInput", [
		"getOption",
		"getArgument",
	]);
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->approot);
}

public function testArgsSet() {
	$map = [
		["approot", $this->approot],
		["port", 8089],
	];

	$this->arguments->method("getOption")
		->will($this->returnValueMap($map));
	$this->arguments->method("getArgument")
		->will($this->returnValueMap($map));

	$server = new Server($this->arguments, true);
	$this->assertContains("php -S=0.0.0.0:8089", $server->processOutput);
	$this->assertContains("-t={$this->approot}/www", $server->processOutput);
	$this->assertContains("/PHP.Gt/src/Cli/Gatekeeper.php",
		$server->processOutput);
}

}#