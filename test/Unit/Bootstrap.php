<?php
/**
 * Test bootstrapper, used by PHPUnit.
 *
 * At the moment this file only has one task to require the Composer autoloader,
 * but is present to allow unit test complexity to grow in the future. May be
 * removed in the future if not required.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Test;

require __DIR__ . "/../../vendor/autoload.php";

class Helper {

/**
 * Recursive function to empty and remove a whole directory.
 *
 * @param string $dirPath Path to directory to remove.
 * @return bool True if directory is successfully removed, otherwise false.
 */
public static function cleanup($dirPath) {
	foreach(new \RecursiveIteratorIterator(
	new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS),
	\RecursiveIteratorIterator::CHILD_FIRST)
	as $path) {

		$path->isDir()
			? rmdir($path->getPathname())
			: unlink($path->getPathname());
	}

	return rmdir($dirPath);
}

}#