<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Api\ApiFactory;
use \Gt\Database\DatabaseFactory;
use \Gt\Response\NotFoundException;
use \Gt\Core\Path;
use \Gt\Logic\LogicFactory;
use Gt\ClientSide\FileOrganiser;

abstract class Dispatcher {

private $appNamespace;
private $request;
private $response;
private $apiFactory;
private $dbFactory;

public function __construct($appNamespace, $request, $response,
$apiFactory, $dbFactory) {
	$this->appNamespace = $appNamespace;
	$this->request = $request;
	$this->response = $response;
	$this->apiFactory = $apiFactory;
	$this->dbFactory = $dbFactory;
}

/**
 * From the provided URI, return the correct path where the source content is
 * located.
 * @param string $uri The requested uri
 * @param string $fixedUri (Pass-by-reference) URI representing
 * the requested file on disk, after being fixed for spelling and or case.
 *
 * @return string The absolute path on disk to the source content file
 */
abstract public function getPath($uri, &$fixedUri);

/**
 * From given file path, return the serialised content. This will either be a
 * raw file representation, or a concatenation or compilation of pre-processed
 * file types (for example, returning the HTML source for a Markdown file).
 *
 * @param string $path The absolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename
 *
 * @return mixed The full, raw source after loading and any optional processing
 */
abstract public function loadSource($path, $filename);

/**
 * Creates a suitable ResponseContent object for the type of dispatcher.
 * For a PageDispatcher, the ResponseContent will be a Gt\Response\Dom\Document.
 * @param mixed $content The serialized content to represent
 *
 * @return ResponseContent The object to serialise as part of the HTTP response
 */
abstract public function createResponseContent($content, $config);

/**
 * Performs the dispatch cycle.
 */
public function process() {
	// Create and assign the Response content. This object may represent a
	// DOMDocument or ApiObject, depending on request type.
	// Get the directory path representing the request.
	$source = "";
	$fullUri = "";
	try {
		$path = $this->getPath($this->request->uri, $fixedUri);
		if($this->request->forceExtension) {
			if(strrpos($fixedUri, ".html")
			!== strlen($fixedUri) - strlen(".html")) {
				$fixedUri .= ".html";
			}
		}
		else {
			if(strrpos($fixedUri, ".html")
			=== strlen($fixedUri) - strlen(".html")) {
				$fixedUri = substr($fixedUri, 0, -strlen(".html"));
			}
		}
		if(strcmp($this->request->uri, $fixedUri) !== 0) {
			// Returning early will cause Start to create a Redirect object.
			return $fixedUri;
		}

		// Get the requested filename, or index filename if none set.
		$filename = $this->getFilename(
			$this->request->uri,
			$this->request->indexFilename,
			$fullUri
		);

		// Load the source view from the path on disk and requested filename.
		$source = $this->loadSource($path, $filename);
	}
	catch(NotFoundException $e) {
		// TODO: Handle 404 error here.
	}

	// Instantiate the response content object, for manipulation in Code.
	$content = $this->createResponseContent($source, $this->response->config);

	// Construct and assign Logic object, which is a collection of
	// Logic class instantiations in order of execution.
	$logicList = LogicFactory::create(
		$this->appNamespace,
		$fullUri,
		$this->apiFactory,
		$this->dbFactory,
		$content
	);

	$this->cleanBuffer();

	// Call the correct methods on each Logic object:
	foreach ($logicList as $logicObj) {
		$logicObj->go();
	}
	foreach (array_reverse($logicList) as $logicObj) {
		if(!method_exists($logicObj, "endGo")) {
			continue;
		}

		$logicObj->endGo();
	}

	$manifest = $content->createManifest($this->request, $this->response);
	$fileOrganiser = new FileOrganiser($this->response, $manifest);
	$fileOrganiser->organise($manifest->pathDetails);
	$manifest->expand();

	$content->flush();
}

/**
 * Gets the name of the requested file in the current directory path, or returns
 * the default index filename if the directory is requested.
 *
 * @param string $uri The requested URI
 * @param string $indexFilename The default index filename, taken from
 * application configuration
 * @param string $fullUri Passed by reference. The full URI, including the
 * implied index filename
 *
 * @return string The filename according the the URI. This may be identical to
 * the requested filename, or may be replaced by the default index filename
 */
public function getFilename($uri, $indexFilename, &$fullUri = null) {
	$filename = basename($uri);
	$fullUri = $uri;

	if(empty($filename)) {
		$filename = $indexFilename;
		$fullUri = implode("/", [$uri, $indexFilename]);
	}

	$pagePath = Path::get(Path::PAGE) . $uri;
	if(is_dir($pagePath)) {
		$filename = $indexFilename;
		$fullUri = implode("/", [$uri, $indexFilename]);
	}

	return $filename;
}

public function cleanBuffer() {
	// TODO: What is the need for this function?
	// ob_start();
	// ob_clean();
}

}#