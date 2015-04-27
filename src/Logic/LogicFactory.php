<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Logic;

use \Gt\Core\Path;
use \Gt\Request\Request;

class LogicFactory {

/**
 * @param string $appNamespace Base namespace containing all application logic
 * @param string $uri The current request URI
 * @param Api $api API Access Layer
 * @param Session $session Session manager
 *
 * @return array An array containing all appropriate Logic objects,
 * depending on request type, in execution order
 */
public static function create($appNamespace, $uri, $api,
$content, $session, $data) {
	$objArray = [];
	$topPath = Path::get(Path::PAGE);

	$filename = basename($uri);
	$path = pathinfo($topPath . $uri, PATHINFO_DIRNAME);
	$logicFileArray = self::getLogicFileArray(
		$filename, $path, $topPath);
	$logicClassNameArray = self::getLogicClassNameArray(
		$appNamespace, $logicFileArray, $topPath);

	foreach ($logicClassNameArray as $className) {
		if(!class_exists($className)) {
			continue;
		}

		$objArray []= new $className(
			$api,
			$content,
			$session,
			$data
		);
	}

	return $objArray;
}

/**
 * Return an array of absolute filepaths to the logic files that match the
 * current URI, in order of execution.
 *
 * @param string $filename The requested filename, without any directory path
 * @param string $path The path to look for logic files in, moving up the tree
 * until and including the $topPath path
 * @param string $topPath The top-most path to use when looking for logic files
 *
 * @return array An array of file paths for each potential logic file, even if
 * there is no file at that path.
 */
public static function getLogicFileArray($filename, $path, $topPath) {
	// Get PageLogic path for current URI.
	$filename = str_replace("-", "_", $filename);
	$currentPageLogicPath = implode("/", [$path, $filename]) . ".php";
	$commonPageLogicPathArray = [];

	$currentPath = $path;
	while(false !== strstr($currentPath, $topPath)) {
		$commonPageLogicPathArray [] = $currentPath . "/_common.php";
		$currentPath = dirname($currentPath);
	}

	$commonPageLogicPathArray = array_reverse($commonPageLogicPathArray);
	$pageLogicPathArray = array_merge(
		$commonPageLogicPathArray,
		[$currentPageLogicPath]
	);

	// Check the case of each Page Logic file
	foreach ($pageLogicPathArray as $i => $path) {
		$pageLogicPathArray[$i] = Path::fixCase($path);
	}

	return $pageLogicPathArray;
}

/**
 * Return an array of Logic class names, in correct execution order,
 * to hand to the Dispatcher where their go methods will be called at the
 * correct time. Each element in the array is fully qualified with the
 * namespace it is contained within.
 *
 * @param string $appNamespace Base namespace containing all application logic
 * @param array $logicPathArray Array of absolute file paths to all Logic
 * classes on disk
 *
 * @return array Array of instantiated Logic objects
 */
public static function getLogicClassNameArray($appNamespace, $logicPathArray) {
	$classNameArray = [];
	$srcPath = Path::get(Path::SRC);

	foreach ($logicPathArray as $logicPath) {
		// Begin creating a string containing the fully-qualified class name.
		$namespaceStr = substr($logicPath, strlen($srcPath) + 1);
		$namespaceStr = strtok($namespaceStr, ".");
		$namespaceStr = str_replace("-", "_", $namespaceStr);
		// Explode the string into an array ..
		$namespaceArray = explode("/", $namespaceStr);
		// .. and add the App's namespace to the beginning of the array.
		array_unshift($namespaceArray, $appNamespace);

		// Implode the array with backslashes to create a FQ class name:
		$classNameArray []= implode("\\", $namespaceArray);
	}

	return $classNameArray;
}

}#
