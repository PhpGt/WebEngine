<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

use \Gt\Core\Path;
use \Gt\Request\Request;

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

	$filename = basename($uri);
	$path = pathinfo($basePath . $uri, PATHINFO_DIRNAME);
	$logicFileArray = self::getLogicFileArray($filename, $path, $basePath);

	return new $objectType($uri, $type, $apiFactory, $dbFactory);
}

/**
 * Return an array of absolute filepaths to the logic files that match the
 * current URI, in order of execution.
 *
 * @param string $filename The requested filename, without any directory path
 * @param string $path The path to look for logic files in, moving up the tree
 * until and including the $topPath path
 * @param string $topPath The top-most path to use when looking for logic files
 */
public function getLogicFileArray($filename, $path, $topPath) {
	// Get PageLogic path for current URI.
	$currentPageLogicPath = implode("/", [$path, $filename]) . ".php";
	$commonPageLogicPathArray = [];

	$currentPath = $path;
	while(false !== strstr($currentPath, $topPath)) {
		$commonPageLogicPathArray [] = $currentPath . "/_common.php";
		$currentPath = dirname($currentPath);
	}

	$commonPageLogicPathArray = array_reverse($commonPageLogicPathArray);
	var_dump($commonPageLogicPathArray, $currentPageLogicPath);
	die("getLogicFileArray");
}

}#