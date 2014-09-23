<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Response\Response;

class FileOrganiser_Test extends \PHPUnit_Framework_TestCase {

private $fileOrganiser;
private $manifest;
private $pathDetails;

public function setUp() {
	$cfg = new \Gt\Core\ConfigObj();
	$response = new Response($cfg);

	$this->manifest = $this->getMockForAbstractClass("\Gt\ClientSide\Manifest");
	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);
	$this->pathDetails = $this->getMock("\Gt\ClientSide\PathDetails", [
		"generatePathDetails",
		"checkValid",
	]);
}

public function tearDown() {

}

public function testFileOrganiserConstructs() {
	$this->assertInstanceOf(
		"\Gt\ClientSide\FileOrganiser",
		$this->fileOrganiser
	);
}

public function testFileOrganiserDoesNotOrganiseWhenValid() {
	$this->manifest->expects($this->any())
		->method("generatePathDetails")
		->will($this->returnValue($this->pathDetails));

	// $this->manifest->expects($this->any())
	// 	->method("checkValid")
	// 	->will($this->returnValue(true));

	$hasOrganisedAnything = $this->fileOrganiser->organise();
	$this->assertFalse($hasOrganisedAnything);
}

}#