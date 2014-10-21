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
use \Gt\Response\Transformer;

class PageDispatcher extends Dispatcher {

private static $pageExtensions = [
	"html",
	"md",
	// "haml",
];

public function getPath($uri, &$fixedUri) {
	$pagePath = Path::get(Path::PAGE);
	$pageDir = Path::fixCase($pagePath . $uri);
	$fixedUri = Path::fixCase($pagePath . $uri, $pagePath);

	if(!is_dir($pageDir)) {
		$pageDir_container = dirname($pageDir);

		if(is_dir($pageDir_container)) {
			if(!file_exists($pagePath . $fixedUri)) {
				throw new NotFoundException($fixedUri);
			}
		}
		else {
			throw new NotFoundException(
				$fixedUri
			);
		}

		$pageDir = $pageDir_container;
	}

	return rtrim($pageDir, "/");
}

public function loadSource($path, $pathFile) {
	$source = "";
	$headerSource = "";
	$footerSource = "";
	$pathFileBase = strtok($pathFile, ".");

	// Look for a header and footer view file up the tree.
	$headerFooterPathTop = dirname(Path::get(Path::PAGE));
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
			$extension = $fileInfo->getExtension();

			switch($specialName) {
			case "header":
				if(empty($headerSource)) {
					$headerSource = file_get_contents($fullPath);

					if(strpos($extension, "htm") !== 0) {
						$headerSource = Transformer::toHtml(
							$headerSource, $extension);
					}
				}
				break;

			case "footer":
				if(empty($footerSource)) {
					$footerSource = file_get_contents($fullPath);

					if(strpos($extension, "htm") !== 0) {
						$footerSource = Transformer::toHtml(
							$footerSource, $extension);
					}
				}
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
		$extension = $fileInfo->getExtension();
		if(!in_array(strtolower($extension), self::$pageExtensions)) {
			// Only include the current file's source if its extension is
			// within the pageExtensions array.
			continue;
		}

		if(strcasecmp($fileBase, $pathFileBase) === 0) {
			$fullPath = implode("/", [$path, $fileName]);
			$source .= file_get_contents($fullPath);
		}

		if(strpos($extension, "htm") !== 0) {
			$source = Transformer::toHtml($source, $extension);
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
	if(!is_string($html)) {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException(
			gettype($html) . " is not a string");
	}
	$domDocument = new \Gt\Dom\Document($html);

	return $domDocument;
}


}#