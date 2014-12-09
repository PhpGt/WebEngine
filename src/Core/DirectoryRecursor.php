<?php
/**
 * A collection of helper methods for iterating directory trees, simplifying
 * the native PHP RecursiveDirectoryIterator class.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class DirectoryRecursor {

/**
 * @param string $directory Absolute path to directory
 * @param callback $callback Function to call for each iteration
 * @param mixed $out Value passed by reference into callback
 * @param int $order One of the RecursiveIteratorIterator's class constants
 *
 * @return array Sorted array containing each callback's return value
 */
public static function walk($directory, $callback, &$out = null,
$order = \RecursiveIteratorIterator::SELF_FIRST) {
	if(!is_dir($directory)) {
		throw new \Gt\Core\Exception\RequiredAppResourceNotFoundException(
			$directory);
	}

	$output = [];

	foreach ($iterator = new \RecursiveIteratorIterator(
	new \RecursiveDirectoryIterator($directory,
	\RecursiveDirectoryIterator::SKIP_DOTS), $order) as $item) {

		$output []= call_user_func_array($callback, [
			$item, $iterator, &$out
		]);
	}

	sort($output);
	return $output;
}

/**
 *
 */
public static function hash($directory) {
	$md5Array = self::walk($directory, "self::hashFile");
	$md5 = implode("", $md5Array);

	return md5($md5);
}

/**
 *
 */
private static function hashFile($file, $iterator) {
	if($file->isDir()) {
		return null;
	}

	$path = $file->getPathname();
	return md5($path) . md5_file($path);
}

/**
 * Removes a directory and all its contents.
 *
 * @return int Number of files and directories removed
 */
public static function purge($path) {
	$count = 0;

	self::walk($path, "self::purgeFile", $count,
		\RecursiveIteratorIterator::CHILD_FIRST);
	rmdir($path);

	return $count;
}

public static function purgeFile($file, $iterator) {
	$pathname = $file->getPathname();

	if($file->isDir()) {
		rmdir($pathname);
		return 1;
	}
	else {
		unlink($pathname);
		return 1;
	}

	return 0;
}

}#