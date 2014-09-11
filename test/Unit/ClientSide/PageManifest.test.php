<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

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

public function tearDown() {}

public function testManifestCreatedFromDocument() {
	$document = new Document($this->html);
	$manifest = $document->createManifest($this->request, $this->response);

	$this->assertInstanceOf("\Gt\ClientSide\Manifest", $manifest);
	$this->assertInstanceOf("\Gt\ClientSide\PageManifest", $manifest);
}

public function testCalculateFingerprint() {
	$scriptStylePathList = [
		"script" => ["/main.js", "/do-something.js", "jqueer.js"],
		"style" => ["/main.css", "/my-font.css", "/more.css"],
	];
	$scriptStyleHtml = "";

	foreach ($scriptStylePathList as $tag => $pathList) {
		foreach ($pathList as $path) {
			$htmlFragment = str_replace(
				$this->scriptStyleTag[$tag], "<%SOURCE_PATH%>", $path);

			$scriptStyleHtml .= $htmlFragment;

			$filePath = Path::get(Path::SRC);
			$fiePath .= "/$tag";
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
	$fingerprint = $manifest->calculateFingerprint(
		$document->querySelector("head"));

	var_dump($fingerprint);die();
}
}#