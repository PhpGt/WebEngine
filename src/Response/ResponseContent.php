<?php
/**
 * The ResponseContent object represents the data that will be passed back to
 * the browser in the response.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Response;

use \Gt\ClientSide\Manifest;

abstract class ResponseContent {

public $config;	// Response config.
public $contentType = "text/plain";

/**
 * By default, a Manifest has no capabilities, but each type of ResponseContent
 * can have its own type of Manifest-extending object, which it should construct
 * and return in an overriden version of this method.
 *
 * @param Request $request The current request object
 * @param Response $response The current response object
 *
 * @return Manifest The Manfiest of correct type according to this object's type
 */
public function createManifest($request, $response) {
	return new Manifest();
}

/**
 * Serialises the response in its current state and adds it to the output
 * buffer, ready for flushing at the end of the response cycle.
 */
public function flush() {
	Headers::add("Content-type", $this->contentType);
	echo $this->__toString();
}

}#