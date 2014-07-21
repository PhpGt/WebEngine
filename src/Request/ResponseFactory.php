<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
class ResponseFactory {

/**
 * Factory method for creating a new Response object. The Response type is
 * abstract, so the Request is used to determine which type of Response to
 * deliver.
 */
public static function create(Request $request, $config) {
	switch($request->getType()) {
	case Request::TYPE_PAGE:
		break;

	case Request::TYPE_SERVICE:
		break;

	default:
		// throw new ?
		break;
	}	
}

}#