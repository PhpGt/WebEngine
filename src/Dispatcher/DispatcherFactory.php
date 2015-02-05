<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

use Gt\Request\Request;
use Gt\Response\Response;
use Gt\Api\Api;
use Gt\Session\Session;
use Gt\Data\Data;

class DispatcherFactory {

/**
 * @param string $appNamespace The root namespace where application code exists
 * @param Request $request Representing the HTTP request
 * @param Response $response Representing the HTTP response
 * @param Api $api API Access Layer
 * @param Session $session Session manager
 * @param Data $data Data factory
 *
 * @return ApiDispatcher|PageDispatcher The appropriate Dispatcher object
 */
public static function createDispatcher($appNamespace, Request $request,
Response $response, Api $api, Session $session, Data $data) {
	$className = "\\Gt\\Dispatcher\\";
	$type = $request->getType();

	switch($type) {
	case Request::TYPE_API:
		$className .= "ApiDispatcher";
		break;

	case Request::TYPE_PAGE:
		$className .= "PageDispatcher";
		break;

	default:
		throw new \Gt\Core\Exception\InvalidAccessException();
	}

	return new $className(
		$appNamespace,
		$request,
		$response,
		$api,
		$session,
		$data
	);
}

}#