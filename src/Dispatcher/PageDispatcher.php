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

	if(!is_dir($pageViewDir)) {
		$pageViewDir_container = dirname($pageViewDir);

		if(!is_dir($pageViewDir_container)) {
			throw new NotFoundException(
				$pageViewDir
			);
		}

		$pageViewDir = $pageViewDir_container;
	}

	return rtrim($pageViewDir, "/");
}

public function loadSource($path, $pathFile) {
	$source = "";
	$headerSource = "";
	$footerSource = "";
	$pathFileBase = strtok($pathFile, ".");

	// Look for a header and footer view file up the tree.
	$headerFooterPathTop = dirname(Path::get(Path::PAGEVIEW));
	$headerFooterPath = realpath($path);
	do {
		foreach (new \DirectoryIterator($headerFooterPath) as $fileInfo) {
			if($fileInfo->isDot()) {
				continue;
			}

			$fileName = $fileInfo->getFilename();
			if($fileName[0] !== "_") {
				continue;
			}

			$fileBase = strtok($fileName, ".");
			$specialName = substr(strtolower($fileBase), 1);
			$fullPath = implode("/", [$headerFooterPath, $fileName]);

			switch($specialName) {
			case "header":
				$headerSource = file_get_contents($fullPath);
				break;

			case "footer":
				$footerSource = file_get_contents($fullPath);
				break;
			}
		}

		// Go up a directory...
		$headerFooterPath = realpath($headerFooterPath . "/..");
		// ... until we are above the Page View directory.
	} while ($headerFooterPath !== $headerFooterPathTop);

	foreach (new \DirectoryIterator($path) as $fileInfo) {
		if($fileInfo->isDot()) {
			continue;
		}

		$fileName = $fileInfo->getFilename();
		$fileBase = strtok($fileName, ".");

		if(strcasecmp($fileBase, $pathFileBase) === 0) {
			$fullPath = implode("/", [$path, $fileName]);
			$source .= file_get_contents($fullPath);
		}
	}

	return implode("\n", [
		$headerSource,
		$source,
		$footerSource,
	]);

	throw new NotFoundException(implode("/", [$path, $pathFile]));
}

public function createResponseContent($html) {

	$domDocument = new \Gt\Response\Dom\Document($html);

	return $domDocument;
}


}#