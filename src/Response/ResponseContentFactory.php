<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
class ResponseContentFactory {

public static function create($type) {
	switch ($type) {
	case Request::TYPE_API:
		break;

	case Request::TYPE_PAGE:
		break;

	default:
		throw new InvalidRequestTypeException();
	}
}

}#