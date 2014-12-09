<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

use \Gt\ClientSide\Manifest;

abstract class ResponseContent {

public $config;	// Response config.

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
	echo $this->__toString();
}

}#