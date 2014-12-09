<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

use Gt\Request\Request;
use Gt\Response\Response;
use Gt\Api\ApiFactory;
use Gt\Database\DatabaseFactory;

class DispatcherFactory {

/**
 * @param string $appNamespace The root namespace where application code exists
 * @param Request $request Representing the HTTP request
 * @param Response $response Representing the HTTP response
 * @param ApiFactory $apiFactory API Access Layer
 * @param DatabaseFactory $databaseFactory Database Access Layer
 *
 * @return ApiDispatcher|PageDispatcher The appropriate Dispatcher object
 */
public static function createDispatcher($appNamespace,
Request $request, Response $response,
ApiFactory $apiFactory, DatabaseFactory $databaseFactory) {
	$type = $request->getType();

	switch($type) {
	case Request::TYPE_API:
		return new ApiDispatcher(
			$appNamespace,
			$request,
			$response,
			$apiFactory,
			$databaseFactory
		);

	case Request::TYPE_PAGE:
		return new PageDispatcher(
			$appNamespace,
			$request,
			$response,
			$apiFactory,
			$databaseFactory
		);

	default:
		throw new \Gt\Core\Exception\InvalidAccessException();
	}
}

}#