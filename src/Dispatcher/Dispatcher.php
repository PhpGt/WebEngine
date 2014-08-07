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

abstract class Dispatcher {

private $request;
private $response;
private $apiFactory;
private $dbFactory;

public function __construct($request, $response, $apiFactory, $dbFactory) {
	$this->request = $request;
	$this->response = $response;
	$this->apiFactory = $apiFactory;
	$this->dbFactory = $dbFactory;
}

/**
 * From the provided URI, return the correct path where the source content is
 * located.
 * @param string $uri The requested uri
 *
 * @return string The absolute path on disk to the source content file
 */
abstract public function getPath($uri);

/**
 * From given file path, return the serialised content. This will either be a
 * raw file representation, or a concatenation or compilation of pre-processed
 * file types (for example, returning the HTML source for a Markdown file).
 *
 * @param string $path The absolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename
 */
abstract public function loadSource($path, $filename);

/**
 * Creates a suitable ResponseContent object for the type of dispatcher.
 * For a PageDispatcher, the ResponseContent will be a Gt\Response\Dom\Document.
 * @param mixed $content The serialized content to represent
 *
 * @return ResponseContent The object to serialise as part of the HTTP response
 */
abstract public function createResponseContent($content);

/**
 * Performs the dispatch cycle.
 */
public function process() {
	// Create and assign the Response content. This object may represent a
	// DOMDocument or ApiObject, depending on request type.
	// Get the directory path representing the request.
	$path = $this->getPath($this->request->uri);
	// Get the requested filename, or index filename if none set.
	$filename = basename($this->request->uri);
	if(empty($filename)) {
		$filename = $this->request->indexFilename;
	}

	// Load the source view from the path on disk and requested filename.
	$source = $this->loadSource($path, $filename);
	// Instantiate the response content object, for manipulation in Code.
	$content = $this->createResponseContent($source);

	// Construct and assign ResponseCode object, which is a collection of
	// Code class instantiations in order of execution.
	// $code = ResponseCodeFactory::create(
	// 	$this->request->uri,
	// 	$this->request->getType(),
	// 	$this->apiFactory,
	// 	$this->dbFactory,
	// 	$content
	// );

	// $this->response->setCode($code);
	// $this->response->setContentObject($content);

	// Handle client-side copying and compilation after the response codes have
	// executed.
	// $this->manifest....
	$content->flush();
}

}#