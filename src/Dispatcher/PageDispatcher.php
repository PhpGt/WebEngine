<?php
/**
 * TODO: Docs
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;

use \Gt\Core\Path;
use \Gt\Response\NotFoundException;

class PageDispatcher extends Dispatcher {

public function getPath($uri, &$fixedUri) {
	$pageViewPath = Path::get(Path::PAGEVIEW);
	$pageViewDir = Path::fixCase($pageViewPath . $uri);
	$fixedUri = Path::fixCase($pageViewPath . $uri, $pageViewPath);
	// Normalise directory path so no return values have trailing slash.
	if(substr($pageViewDir, -1) === "/") {
		$pageViewDir = substr($pageViewDir, 0, -1);
	}

	if(!is_dir($pageViewDir)) {
		$pageViewDir_container = dirname($pageViewDir);

		if(!is_dir($pageViewDir_container)) {
			throw new NotFoundException(
				$pageViewDir
			);
		}

		$pageViewDir = $pageViewDir_container;
	}

	return $pageViewDir;
}

public function loadSource($path, $pathFile) {
	// Only load .html files (for now).
	foreach (new \DirectoryIterator($path) as $fileInfo) {
		if($fileInfo->isDot()) {
			continue;
		}

		$fileName = $fileInfo->getFilename();
		$fileBase = strtok($fileName, ".");

		if(strcasecmp($fileBase, $pathFile) === 0) {
			$fullPath = implode("/", [$path, $fileName]);
			return file_get_contents($fullPath);
		}
	}

	throw new NotFoundException(implode("/", [$path, $pathFile]));
}

public function createResponseContent($html) {

	$domDocument = new \Gt\Response\Dom\Document($html);

	return $domDocument;
}


}#