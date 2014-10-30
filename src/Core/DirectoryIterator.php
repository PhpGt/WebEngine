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

class DirectoryIterator {

/**
 *
 */
public static function walk($directory, $callback, &$out = null) {
	if(!is_dir($directory)) {
		throw new \Gt\Core\Exception\RequiredAppResourceNotFoundException(
			$directory);
	}

	$output = [];

	foreach ($iterator = new \RecursiveIteratorIterator(
	new \RecursiveDirectoryIterator($directory,
		\RecursiveDirectoryIterator::SKIP_DOTS),
	\RecursiveIteratorIterator::SELF_FIRST) as $item) {

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

}#