<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;
use Gt\Request\Request;

class DispatcherFactory {

/**
 * @param Request $request Representing the HTTP request
 * @param Obj $config Object contining response configuration properties
 * @return Dispatcher Either an ApiDispatcher or PageDispatcher
 */
public static function create(Request $request, $config) {
	$type = $request->getType();

	switch($type) {
	case Request::TYPE_API:
		return new ApiDispatcher();
		break;

	case Request::TYPE_PAGE:
		return new PageDispatcher();
		break;

	default:
		break;
	}
}

}#