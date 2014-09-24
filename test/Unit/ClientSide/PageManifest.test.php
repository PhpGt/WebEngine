<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Dom\Document;

class PageManifest_Test extends \PHPUnit_Framework_TestCase {

private $scriptStyleTag = [
	"script" => "<script src=\"<%SOURCE_PATH%>\"></script>",
	"style" => "<link rel=\"stylesheet\" href=\"<%SOURCE_PATH%>\" />",
];
private $html = <<<HTML
<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Test page</title>
	<%SCRIPT_STYLE_LIST%>
</head>
<body>
	<h1>Test page</h1>
</body>
</html>
HTML;

private $request;
private $response;

public function setUp() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();

	$cfg = new \Gt\Core\ConfigObj();

	$this->request		= new Request("/", $cfg);
	$this->response		= new Response($cfg);
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

public function testManifestCreatedFromDocument() {
	$document = new Document($this->html);
	$manifest = $document->createManifest($this->request, $this->response);

	$this->assertInstanceOf("\Gt\ClientSide\Manifest", $manifest);
	$this->assertInstanceOf("\Gt\ClientSide\PageManifest", $manifest);
}

public function testCalculateFingerprint() {
	$scriptStylePathList = [
		"script" => ["/main.js", "/do-something.js", "/jqueer.js"],
		"style" => ["/main.css", "/my-font.css", "/more.css"],
	];
	$scriptStyleHtml = "";

	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$htmlFragment = str_replace(
				"<%SOURCE_PATH%>",
				"/$tag$path",
				$this->scriptStyleTag[$tag]
			);

			$scriptStyleHtml .= $htmlFragment;

			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			// path concatenated with path, to make it easy to remember, but
			// to avoid common mistake within actual implementation of
			// accidentally hashing the path and not the file contents.
			$fileContents = md5($path . $path);
			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}
			file_put_contents($filePath, $fileContents);
		}
	}

	$html = str_replace("<%SCRIPT_STYLE_LIST%>", $scriptStyleHtml, $this->html);
	$document = new Document($html);

	$manifest = $document->createManifest($this->request, $this->response);
	$details = new PathDetails(
		$document->head->xpath(PageManifest::$xpathQuery));
	$fingerprint = $manifest->calculateFingerprint($details);

	$expectedFingerprint = "";
	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			$expectedFingerprint .= md5_file($filePath);
		}
	}
	$expectedFingerprint = md5($expectedFingerprint);
	$this->assertEquals($expectedFingerprint, $fingerprint);
}

public function testFingerprintIgnoresExternalFiles() {
	// An external file has a double slash in it e.g. http://something.example,
	// and these files cannot be fingerprinted.
	$scriptStylePathList = [
		"script" => ["/main.js", "http://example.com/javascript.js"],
		"style" => ["/main.css", "//example.com/external.css"],
	];
	$scriptStyleHtml = "";

	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$htmlFragment = str_replace(
				"<%SOURCE_PATH%>",
				"/$tag$path",
				$this->scriptStyleTag[$tag]
			);

			$scriptStyleHtml .= $htmlFragment;

			if(strstr($path, "//")) {
				// Add external files to head, but don't create files.
				continue;
			}

			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			// path concatenated with path, to make it easy to remember, but
			// to avoid common mistake within actual implementation of
			// accidentally hashing the path and not the file contents.
			$fileContents = md5($path . $path);
			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}
			file_put_contents($filePath, $fileContents);
		}
	}

	$html = str_replace("<%SCRIPT_STYLE_LIST%>", $scriptStyleHtml, $this->html);
	$document = new Document($html);

	$manifest = $document->createManifest($this->request, $this->response);
	$details = new PathDetails(
		$document->head->xpath(PageManifest::$xpathQuery));
	$fingerprint = $manifest->calculateFingerprint($details);

	$expectedFingerprint = "";
	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			if(!strstr($path, "//")) {
				$expectedFingerprint .= md5_file($filePath);
			}
		}
	}
	$expectedFingerprint = md5($expectedFingerprint);
	$this->assertEquals($expectedFingerprint, $fingerprint);
}

public function testRelativeUris() {
	$scriptStylePathList = [
		"script" => ["/main.js", "directory/nested.js"],
		"style" => ["/main.css", "directory/subDir/deepFile.css"],
	];
	$scriptStyleHtml = "";

	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$htmlFragment = "";

			if(substr($path, 0, 1) === "/") {
				$htmlFragment = str_replace(
					"<%SOURCE_PATH%>",
					"/$tag$path",
					$this->scriptStyleTag[$tag]
				);
			}
			else {
				$htmlFragment = str_replace(
					"<%SOURCE_PATH%>",
					$path,
					$this->scriptStyleTag[$tag]
				);
			}

			$scriptStyleHtml .= $htmlFragment;

			if(substr($path, 0, 1) !== "/") {
				// Add relative files to head, but don't create files.
				continue;
			}

			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			// path concatenated with path, to make it easy to remember, but
			// to avoid common mistake within actual implementation of
			// accidentally hashing the path and not the file contents.
			$fileContents = md5($path . $path);
			if(!is_dir(dirname($filePath))) {
				if(false === (mkdir(dirname($filePath), 0775, true)) ) {
					die("mkdir failed :(");
				}
			}

			file_put_contents($filePath, $fileContents);
		}
	}

	$html = str_replace("<%SCRIPT_STYLE_LIST%>", $scriptStyleHtml, $this->html);
	$document = new Document($html);

	$this->request->uri = "/directory/index.html";

	$manifest = $document->createManifest($this->request, $this->response);
	$details = new PathDetails(
		$document->head->xpath(PageManifest::$xpathQuery));
	$fingerprint = $manifest->calculateFingerprint($details);

	$expectedFingerprint = "";
	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			if(substr($path, 0, 1) !== "/") {
				continue;
			}

			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			$expectedFingerprint .= md5_file($filePath);
		}
	}

	$expectedFingerprint = md5($expectedFingerprint);
	$this->assertEquals($expectedFingerprint, $fingerprint);
}

public function testCheckValidFromGivenFingerprint() {
	$scriptStylePathList = [
		"script" => ["/main.js", "/do-something.js", "/jqueer.js"],
		"style" => ["/main.css", "/my-font.css", "/more.css"],
	];
	$scriptStyleHtml = "";

	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$htmlFragment = str_replace(
				"<%SOURCE_PATH%>",
				"/$tag$path",
				$this->scriptStyleTag[$tag]
			);

			$scriptStyleHtml .= $htmlFragment;

			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			// path concatenated with path, to make it easy to remember, but
			// to avoid common mistake within actual implementation of
			// accidentally hashing the path and not the file contents.
			$fileContents = md5($path . $path);
			if(!is_dir(dirname($filePath))) {
				mkdir(dirname($filePath), 0775, true);
			}
			file_put_contents($filePath, $fileContents);
		}
	}

	$html = str_replace("<%SCRIPT_STYLE_LIST%>", $scriptStyleHtml, $this->html);
	$document = new Document($html);

	$manifest = $document->createManifest($this->request, $this->response);
	$details = new PathDetails(
		$document->head->xpath(PageManifest::$xpathQuery));

	$expectedFingerprint = "";
	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$filePath = Path::get(Path::SRC);
			$filePath .= "/$tag";
			$filePath .= $path;

			$expectedFingerprint .= md5_file($filePath);
		}
	}
	$expectedFingerprint = md5($expectedFingerprint);

	$this->assertTrue($manifest->checkValid($expectedFingerprint));
}

public function testCheckValidFromWwwFiles() {
	// TODO:
	// 1. Create normal manifest as above.
	// 2. Copy over files to www directory (into hashed directories).
	// 3. Check validity.
	// 4. Add another file to the head.
	// 5. Check invalidity.
}

}#