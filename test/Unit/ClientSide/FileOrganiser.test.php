<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Core\Path;

class FileOrganiser_Test extends \PHPUnit_Framework_TestCase {

private $fileOrganiser;
private $manifest;
private $pathDetails;

private $tmp;

public function setUp() {
	$tmp = \Gt\Test\Helper::createTmpDir();

	$document = new \Gt\Dom\Document();

	$cfg = new \Gt\Core\ConfigObj();
	$request = new Request("/", $cfg);
	$response = new Response($cfg);

	// TODO: Update usage of Manifest to Mocked abstract class.
	$this->manifest = new PageManifest($document->head, $request, $response);
	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);
	$this->pathDetails = $this->getMock("\Gt\ClientSide\PathDetails");
}

public function tearDown() {
	\Gt\Test\Helper::cleanup(Path::get(Path::WWW));
	\Gt\Test\Helper::cleanup(Path::get(Path::ASSET));
	\Gt\Test\Helper::cleanup(Path::get(Path::SCRIPT));
	\Gt\Test\Helper::cleanup(Path::get(Path::STYLE));
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

public function textAssetInvalidWithEmptyWWW() {
	$this->assertFalse($this->fileOrganiser->checkAssetValid());
}

public function testAssetInvalidates() {
	$dir = $this->getPath(Path::ASSET);
	file_put_contents("$dir/file.txt", "dummy data");

	$this->assertFalse($this->fileOrganiser->checkAssetValid());
}

private function getPath($path) {
	$dir = Path::get($path);
	if(!is_dir($dir)) {
		mkdir($dir, 0775, true);
	}

	return $dir;
}

}#