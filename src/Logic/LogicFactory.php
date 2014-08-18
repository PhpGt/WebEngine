<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

class LogicFactory {

/**
 * @param string $uri The current request URI
 * @param string $type Request Class constant, the type of request
 * @param ApiFactory $apiFactory API Access Layer
 * @param DatabaseFactory $dbFactory Database Access Layer
 *
 * @return Logic The appropriate Logic object, depending on request type
 */
public static function create($uri, $type, $apiFactory, $dbFactory) {
	$objectType = null;
	$basePath = null;

	switch ($type) {
	case Request::TYPE_PAGE:
		$objectType = "PageLogic";
		$basePath = Path::get(Path::PAGELOGIC);
		break;

	case Request::TYPE_API:
		$objectType = "ApiLogic";
		$basePath = Path::get(Path::APILOGIC);
		break;

	default:
		throw new \Gt\Core\Exception\InvalidAccessException();
	}

	var_dump($basePath);die("LOGIC FACTORY!!!!!!!!!");

	return new $objectType($uri, $type, $apiFactory, $dbFactory);
}

}#