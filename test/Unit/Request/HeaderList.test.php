<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

class HeaderList_Test extends \PHPUnit_Framework_TestCase {

private $server = [
	"HTTP_ACCEPT" 			=> "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
	"HTTP_ACCEPT_ENCODING" 	=> "gzip,deflate,sdch",
	"HTTP_COOKIE" 			=> "FIRST=Les; LAST=McQueen; BAND=crème brûlée",
	"HTTP_USER_AGENT" 		=> "PHPUnit/PHP.Gt (http://php.gt)",
];

public function testArrayAccess() {
	$headers = new HeaderList($this->server);

	$this->assertEquals(
		$this->server["HTTP_ACCEPT"], $headers["accept"]);
	$this->assertEquals(
		$this->server["HTTP_ACCEPT_ENCODING"], $headers["accept-encoding"]);
	$this->assertEquals(
		$this->server["HTTP_COOKIE"], $headers["cookie"]);
	$this->assertEquals(
		$this->server["HTTP_USER_AGENT"], $headers["user-agent"]);
}

public function testProperty() {
	$headers = new HeaderList($this->server);

	$this->assertEquals(
		$this->server["HTTP_ACCEPT"], $headers->accept);
	$this->assertEquals(
		$this->server["HTTP_ACCEPT_ENCODING"], $headers->accept_encoding);
	$this->assertEquals(
		$this->server["HTTP_COOKIE"], $headers->cookie);
	$this->assertEquals(
		$this->server["HTTP_USER_AGENT"], $headers->user_agent);
}

public function testCase() {
	$headers = new HeaderList($this->server);

	$this->assertEquals(
		$this->server["HTTP_ACCEPT"], $headers["Accept"]);
	$this->assertEquals(
		$this->server["HTTP_ACCEPT"], $headers->Accept);
}

public function testReadOnly() {
	$headers = new HeaderList($this->server);
	$this->setExpectedException("\Gt\Core\Exception\InvalidAccessException");

	$headers["accept"] = "<?php system('echo \"HAX!\" && rm -rf /');";
}

public function testNotUnsettable() {
	$headers = new HeaderList($this->server);
	$this->setExpectedException("\Gt\Core\Exception\InvalidAccessException");

	unset($headers["accept"]);
}

public function testIsset() {
	$headers = new HeaderList($this->server);
	$this->assertTrue(isset($headers["Accept"]));
}

}#