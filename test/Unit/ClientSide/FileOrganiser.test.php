<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Request\Request;
use \Gt\Response\Response;

class FileOrganiser_Test extends \PHPUnit_Framework_TestCase {

private $fileOrganiser;
private $manifest;
private $pathDetails;

public function setUp() {
	$document = new \Gt\Dom\Document();

	$cfg = new \Gt\Core\ConfigObj();
	$request = new Request("/", $cfg);
	$response = new Response($cfg);

	// TODO: Update usage of Manifest to Mocked abstract class.
	$this->manifest = new PageManifest($document->head, $request, $response);
	// $this->manifest = $this->getMockBuilder("\Gt\ClientSide\Manifest")
	// 	->setMethods(["generatePathDetails"])
	// 	->getMock();

	// $this->manifest->expects($this->any())
	// 	->method("generatePathDetails")
	// 	->willReturn($this->pathDetails);

	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);
	$this->pathDetails = $this->getMock("\Gt\ClientSide\PathDetails");
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
	$pathDetails = $this->manifest->generatePathDetails();

	$hasOrganisedAnything = $this->fileOrganiser->organise($pathDetails);
	$this->assertFalse($hasOrganisedAnything);
}

// public function

}#