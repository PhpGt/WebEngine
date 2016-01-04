<?php
/**
 * A collection of helper methods for iterating directory trees, simplifying
 * the native PHP RecursiveDirectoryIterator class.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

class DirectoryRecursor {

const ORDER_SELF_FIRST  = RecursiveIteratorIterator::SELF_FIRST;
const ORDER_CHILD_FIRST = RecursiveIteratorIterator::CHILD_FIRST;

/**
 * @param string $directory Absolute path to directory
 * @param callback $callback Function to call for each iteration
 * @param mixed $out Value passed by reference into callback
 * @param int $order One of the RecursiveIteratorIterator's class constants
 *
 * @return array Sorted array containing each callback's return value
 */
public static function walk($directory, $callback, &$out = null,
$order = self::ORDER_SELF_FIRST) {
	if(!is_dir($directory)) {
		throw new \Gt\Core\Exception\RequiredAppResourceNotFoundException(
			$directory);
	}

	$output = [];

	foreach ($iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($directory,
	RecursiveDirectoryIterator::SKIP_DOTS), $order) as $item) {

		$output []= call_user_func_array($callback, [
			$item, $iterator, &$out
		]);
	}

	$output = array_filter($output);
	sort($output);
	return $output;
}

/**
 * Recursively hashes a whole directory tree, providing a single hash
 * representation of all files' content and paths.
 *
 * @param string $directory Absolute path to directory
 *
 * @return string Hash representation of directory's content
 *
 * @uses self::walk Uses the walk method to recursively iterate over the
 * directory
 */
public static function hash($directory) {
	$md5Array = self::walk($directory, "self::hashFile");
	$md5 = implode("", $md5Array);

	return md5($md5);
}

/**
 * Returns a hash of a file's content and its path, meaning that if either the
 * content or the file name is modified, so will the hash.
 *
 * @param \SplFileInfo $file Iterator's current item
 * @param \Iterator $iterator Current iterator being used in the self::walk
 * method
 *
 * @return string Hash representation of file
 *
 * @used-by self::hash to build up a hash of all files contained within a
 * directory
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
 * @param string $path Absolute file path to purge. If the provided path is to
 * a directory, it will be recursively purged
 *
 * @return int Number of files and directories removed
 */
public static function purge($path) {
	$count = 0;

	self::walk($path, "self::purgeFile", $count,
		RecursiveIteratorIterator::CHILD_FIRST);
	rmdir($path);

	return $count;
}

/**
 * Removes the provided file or directory, returning the number of successful
 * operations (for use in other functions to count total operations).
 *
 * @param \SplFileInfo $path Iterator's current item
 * @param \Iterator $iterator Iterator used by walk method
 *
 * @return int Number of successful operations
 */
public static function purgeFile($file) {
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
