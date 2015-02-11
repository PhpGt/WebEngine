<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
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
	$this->assertFalse($this->fileOrganiser->checkStaticValid());
}

public function testAssetInvalidatesFromEmpty() {
	$dir = $this->getPath(Path::ASSET);
	file_put_contents("$dir/file.txt", "dummy data");

	$this->assertFalse($this->fileOrganiser->checkStaticValid());
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

	$this->assertFileExists(Path::get(Path::WWW) . "/static-fingerprint");

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

	$this->assertFalse($this->fileOrganiser->checkStaticValid(),
		"assets should be invalid before copy");

	$copyCount = $this->fileOrganiser->copyAsset();

	$this->assertTrue($this->fileOrganiser->checkStaticValid(),
		"assets should be valid after copy");
}

public function testAssetInvalidatesAfterSourceUpdated() {
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

	$this->fileOrganiser->copyAsset();

	file_put_contents("$dir/$fileArray[0]", "updated!");

	$this->assertFalse($this->fileOrganiser->checkStaticValid(),
		"assets should be invalid after source updated");
}

public function testAssetCopyCreatesCorrectHash() {
	// Check hash is 0000000 when no source assets.
	$dir = $this->getPath(Path::ASSET);
	$wwwDir = Path::get(Path::WWW);
	$staticFingerprintFile = "$wwwDir/static-fingerprint";

	mkdir("$dir/directory");
	$this->fileOrganiser->copyAsset();
	$this->fileOrganiser->createStaticFingerprint();

	$staticFingerprint = file_get_contents($staticFingerprintFile);
	$this->assertContains(str_pad("", 32, "0"), $staticFingerprint,
		"asset fingerprint should be all zeroes when no files present");

	// Check hash is correct when source assets are copied.
}

public function testAssetValidIfNoSourceDirectory() {
	$this->assertTrue($this->fileOrganiser->checkStaticValid());
}

public function testAssetDoesNotCopyIfNoSourceDirectory() {
	$this->assertEquals(0, $this->fileOrganiser->copyAsset(),
		"shouldn't copy any files if no source directory present");
}

public function testStaticSourceFileEditInvalidates() {
	// TODO.
}

public function testOrganiseFunctionCopiesAssets() {
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

	$this->assertEquals(count($fileArray), $this->fileOrganiser->organise(),
		"organise method should copy the correct number of files");
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

/**
 * @expectedException \Gt\ClientSide\CompilerParseException
 */
public function testDoesNotCopyIfErrorInSource() {
	$dir = $this->getPath(Path::STYLE);
	$filename = "/broken-style.scss";

	file_put_contents($dir . $filename, "malformed { opacity: opacity: }");

	$html = "<!doctype html>
	<html><head>
		<link rel='stylesheet' href='/Style$filename' />
	</head><body><h1>Test</h1></body></html>";
	$document = new \Gt\Dom\Document($html);
	$cfg = new \Gt\Core\ConfigObj();
	$request = new Request("/", $cfg);
	$response = new Response($cfg);
	$this->manifest = new PageManifest($document->head, $request, $response);

	$this->fileOrganiser = new FileOrganiser($response, $this->manifest);
	$pathDetails = $this->manifest->generatePathDetails();

	$copyCount = $this->fileOrganiser->copyCompile($pathDetails);
	$this->assertEquals(0, $copyCount);

	$this->assertFileNotExists(Path::get(Path::WWW) . $filename);
}

public function testPurgeStaticWwwFilesRemovesStaticFingerprintFile() {
	$staticFingerprintFile = $this->fileOrganiser->getStaticFingerprintFile();
	$fingerprintPath = $this->getPath($staticFingerprintFile);
	file_put_contents($staticFingerprintFile, "test-fingerprint");

	$this->fileOrganiser->purgeStaticWwwFiles();
	$this->assertFileNotExists($staticFingerprintFile,
		'Static fingerprint file should not exist after purge');
}

public function testPurgeStaticWwwFilesPurgesAssetStyleScriptButLeavesOthers() {
	$wwwDir = $this->getPath(Path::WWW);
	$directoriesToCreate = ["Asset", "Script", "Style"];
	$filesThatShouldStay = ["sitemap.xml", "favicon.ico", "robots.txt"];

	foreach ($directoriesToCreate as $dir) {
		$dirName = $dir . "-" . uniqid();
		$dirPath = "$wwwDir/$dirName";

		mkdir($dirPath, 0775, true);

		// Create some random files inside.
		for ($i = 0; $i < 5; $i++) {
			touch($dirPath . "/" . uniqid());
		}
	}

	foreach ($filesThatShouldStay as $file) {
		$filePath = "$wwwDir/$file";
		file_put_contents($filePath, uniqid());
	}

	$this->fileOrganiser->purgeStaticWwwFiles();

	foreach ($directoriesToCreate as $dir) {
		$dirName = $dir . "-" . uniqid();
		$dirPath = "$wwwDir/$dirName";

		$this->assertFileNotExists($dirPath,
			'www subdirectory should not exist');
	}

	foreach ($filesThatShouldStay as $file) {
		$filePath = "$wwwDir/$file";

		$this->assertFileExists($filePath, 'www file should exist');
	}
}

private function getPath($path) {
	$dir = null;

	try {
		$dir = Path::get($path);
	}
	catch(\UnexpectedValueException $e) {
		$dir = dirname($path);
	}

	if(!is_dir($dir)) {
		mkdir($dir, 0775, true);
	}

	return $dir;
}

}#