<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Response\ResponseCode;
use \Gt\Response\Headers;
use \Gt\Api\Api;
use \Gt\Response\NotFoundException;
use \Gt\Core\Path;
use \Gt\Logic\LogicFactory;
use Gt\ClientSide\FileOrganiser;

abstract class Dispatcher {

private $appNamespace;
private $request;
private $response;
private $api;
private $session;
private $data;

protected static $validExtensions = [];

public function __construct($appNamespace, $request, $response,
$api, $session, $data) {
	$this->appNamespace = $appNamespace;
	$this->request = $request;
	$this->response = $response;
	$this->api = $api;
	$this->session = $session;
	$this->data = $data;
}

/**
 * Returns the upper-most directory available to the type of dispatcher used,
 * for instance src/Page or src/Api.
 *
 * @return string Absolute path of directory
 */
abstract public function getBasePath();

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
 * Headers' and footers' source will be attached accordingly if available.
 *
 * @param string $path The absolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename, with extension
 *
 * @return mixed The full, raw source after loading and any optional processing,
 * including header and footer data
 */
abstract public function loadSource($path, $filename);

/**
 * From given file path, return the serialised content of an error page for the
 * provided response code.
 *
 * @param string $path The abolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename, with extension
 *
 * @return mixel The full, raw source of the error response
 */
abstract public function loadError($path, $filename, $responseCode);

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
	$responseCode = $this->response->code;
	$dynamicFilePath = $this->getDynamicFilePath($this->request->uri);
	$isDynamic = false;

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
		// Handle 404 error here.
		$path = $this->getPath($this->request->uri, $fixedUri, false);
		$filename = $this->getFilename(
			$this->request->uri,
			$this->request->indexFilename,
			$fullUri
		);

		if(is_null($dynamicFilePath)) {
			$responseCode->set(404);
			$source = $this->loadError($path, $filename, $responseCode->get());
		}
		else {
			$isDynamic = true;
			$dynamicPathInfo = new \SplFileInfo($dynamicFilePath);
			$dynamicPath = $dynamicPathInfo->getPath();
			$dynamicFilename = $dynamicPathInfo->getFilename();

			$source = $this->loadSource($dynamicPath, $dynamicFilename);
		}
	}

	// Instantiate the response content object, for manipulation in Code.
	$content = $this->createResponseContent($source, $this->response->config);
	$this->setContentUri($this->request->uri, $content);
	$this->cleanBuffer();

	// Only execute Logic if the response is a success.
	if($responseCode->getType() === ResponseCode::TYPE_SUCCESS) {
		// Construct and assign Logic object, which is a collection of
		// Logic class instantiations in order of execution.
		$logicList = LogicFactory::create(
			$this->appNamespace,
			$fullUri,
			$this->api,
			$content,
			$this->session,
			$this->data
		);

		// Call the correct methods on each Logic object:
		foreach ($logicList as $logicObj) {
			$logicObj->isDynamic = $isDynamic;
			if(method_exists($logicObj, "before")) {
				$logicObj->before();
			}
			$logicObj->go();

			$data = array_merge($_GET, $_POST);
			if(!empty($data["do"])) {
				$action = $data["do"];
				unset($data["do"]);
				$doMethodUnderscore = "do_$action";
				$doMethodCamel = "do" . ucfirst($action);

				if(method_exists($logicObj, $doMethodUnderscore)) {
					$logicObj->$doMethodUnderscore($data);
				}
				else if(method_exists($logicObj, $doMethodCamel)) {
					$logicObj->$doMethodCamel($data);
				}
			}

			if(method_exists($logicObj, "after")) {
				$logicObj->after();
			}
		}
		foreach (array_reverse($logicList) as $logicObj) {
			if(!method_exists($logicObj, "endGo")) {
				continue;
			}

			$logicObj->endGo();
		}
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

/**
 * Looks up the tree for a _dynamic page with a valid file extension.
 *
 * @param string $requestDir Requested URI
 *
 * @return null|string Absolute path to the _dynamic file on disk, or null if
 * none found
 */
public function getDynamicFilePath($uri) {
	// Base path is the path to the first child directory of $requestDir
	// within the src path (typically, Page or Api).
	$src = Path::get(Path::SRC);
	$basePath = $this->getBasePath();
	$requestDir = Path::fixCase($basePath . $uri);
	$basePath = substr(
		$requestDir,
		0,
		strpos($requestDir, "/", strlen($src) + 1)
	);

	// Look up the tree until basepath is met.
	$searchDirectory = $requestDir;
	do {
		if(is_dir($searchDirectory)) {
			foreach(new \DirectoryIterator($searchDirectory) as $file) {
				if($file->isDot()
				|| $file->isDir()) {
					continue;
				}

				$ext = $file->getExtension();
				if(!in_array($ext, $this::$validExtensions)) {
					continue;
				}

				$filename = $file->getFilename();
				$info = $file->getPathInfo();
				$filenameNoExt = pathinfo($filename, PATHINFO_FILENAME);
				if($filenameNoExt === "_dynamic") {
					return "$searchDirectory/$filename";
				}
			}
		}

		$searchDirectory = substr(
			$searchDirectory,
			0,
			strrpos($searchDirectory, "/")
		);

	} while($searchDirectory !== dirname($basePath));

	return null;
}

public function cleanBuffer() {
	// TODO: What is the need for this function?
	// ob_start();
	// ob_clean();
}

}#
