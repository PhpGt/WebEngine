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
	\Gt\Test\Helper::cleanup(Path::get(Path::ROOT));
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

public function testAssetInvalidatesFromEmpty() {
	$dir = $this->getPath(Path::ASSET);
	file_put_contents("$dir/file.txt", "dummy data");

	$this->assertFalse($this->fileOrganiser->checkAssetValid());
}

public function testAssetCopies() {
	$dir = $this->getPath(Path::ASSET);
	$fileArray = [
		"text-file.txt",
		"picture.jpg",
		"directory/markdown-file.md",
		"directory/another-text-file.txt",
	];
	foreach ($fileArray as $file) {
		$filePath = "$dir/$file";
		if(!is_dir(dirname($filePath))) {
			mkdir(dirname($filePath), 0775, true);
		}

		file_put_contents($filePath, uniqid() . "\n$file\n" . uniqid() );
	}

	$copyCount = $this->fileOrganiser->copyAsset();
	$this->assertEquals(count($fileArray), $copyCount);

	$this->assertFileExists(Path::get(Path::WWW) . "/asset-fingerprint");

	// Check contents of copied files.
	$wwwAssetDir = Path::get(Path::WWW) . "/Asset";
	foreach ($fileArray as $file) {
		$wwwFilePath = "$wwwAssetDir/$file";
		$filePath = "$dir/$file";
		$wwwFileContents = file_get_contents($wwwFilePath);
		$sourceFileContents = file_get_contents($filePath);

		$this->assertEquals($sourceFileContents, $wwwFileContents,
			"www asset file should have the same contents as the source file");
	}
}

public function testAssetValidAfterCopy() {

}

public function testAssetInvalidatesAfterCopy() {

}

public function testAssetCopyCreatesCorrectHash() {
	// Check hash is 0000000 when no source assets.

	// Check hash is correct when source assets are copied.
}

public function testOrganiserMinifies() {
	$dir = $this->getPath(Path::SCRIPT);
	$publicDir = substr($dir, strlen(Path::get(Path::SRC)));
	file_put_contents("$dir/my-script.js", "console.log('hello, test!');");

	$document = new \Gt\Dom\Document();
	$document->head->appendChild($document->createElement("script", [
		"src" => $publicDir
	]));

	$cfg = new \Gt\Core\ConfigObj(["client_minified" => true]);
	$request = new Request("/", $cfg);
	$response = new Response($cfg);

	$this->manifest = new PageManifest($document->head, $request, $response);
	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);

	$this->assertTrue($this->fileOrganiser->organise(
		$this->manifest->generatePathDetails()
	));
}

public function data_uriList() {
	$typeList = ["script" => "js", "style" => "css"];
	$paramCallList = [];
	$uriList = [];

	foreach ($typeList as $type => $ext) {
		$dir = $type;
		$file = uniqid() . "-$type.$ext";


		for($nesting = 0; $nesting < 5; $nesting++) {
			$uri = "/$dir";

			for($i = 0; $i < $nesting; $i++) {
				$uri .= "/" . uniqid();
			}

			$uri .= "/$file";
			$uriList []= $uri;
		}
	}

	$paramCallList []= [$uriList];
	return $paramCallList;
}
/**
 * @dataProvider data_uriList
 */
public function testOrganiserCopiesListOfFiles($uriList) {
	$srcPath = Path::get(Path::SRC);
	$document = new \Gt\Dom\Document();

	// Add element to the head and put dummy content on-disk.
	foreach ($uriList as $uri) {
		$type = substr($uri, 1, strpos($uri, "/", 1) - 1);
		$el = null;
		$path = $srcPath . $uri;
		if(!is_dir(dirname($path))) {
			mkdir(dirname($path), 0775, true);
		}

		if($type === "script") {
			$el = $document->createElement("script", [
				"src" => $uri,
			]);
			file_put_contents($path, "console.log(\"script: $uri\");");
		}
		else if($type === "style") {
			$el = $document->createElement("link", [
				"rel" => "stylesheet",
				"href" => $uri,
			]);
			file_put_contents($path, "/*style: $uri*/ body{background: red;}");
		}

		$document->head->appendChild($el);
	}

	$cfg = new \Gt\Core\ConfigObj();
	$request = new Request("/", $cfg);
	$response = new Response($cfg);

	$this->manifest = new PageManifest($document->head, $request, $response);
	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);
	$fingerprint = $this->manifest->fingerprint;
	$pathDetails = $this->manifest->generatePathDetails();
	$pathDetails->setFingerprint($fingerprint);
	$this->fileOrganiser->organise($pathDetails);

	$wwwPath = Path::get(Path::WWW);

	foreach ($uriList as $uri) {
		$type = substr($uri, 1, strpos($uri, "/", 1) - 1);
		$uriAfterType = "-$fingerprint" . substr($uri, strpos($uri, "/", 1));
		$path = $wwwPath . "/$type" . $uriAfterType;

		$this->assertFileExists($path);
	}
}

private function getPath($path) {
	$dir = Path::get($path);
	if(!is_dir($dir)) {
		mkdir($dir, 0775, true);
	}

	return $dir;
}

}#