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
use \Gt\Page\Transformer;

class PageDispatcher extends Dispatcher {

private static $pageExtensions = [
	"html",
	Transformer::TYPE_MARKDOWN,
];

/**
 * Gets the absolute file path of a source file according to the provided URI,
 * and updates the fixedUri parameter accordingly.
 *
 * @param string $uri The URI as requested by the browser
 * @param string $fixedUri The URI after it has been adjusted
 *
 * @return string The absolute file path on disk of the source file
 */
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

/**
 * From given file path, return the serialised content. This will either be a
 * raw file representation, or a concatenation or compilation of pre-processed
 * file types (for example, returning the HTML source for a Markdown file).
 * Headers' and footers' source will be attached accordingly if available.
 *
 * @param string $path The absolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename, with extension
 *
 * @return mixed The full, raw source after loading and any optional processing,
 * including header and footer data
 */
public function loadSource($path, $filename) {
	$source = "";
	$headerSource = "";
	$footerSource = "";
	$pathFileBase = strtok($filename, ".");

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

			switch($specialName) {
			case "header":
				$this->readSourceContent($fullPath, $headerSource, true);
				break;

			case "footer":
				$this->readSourceContent($fullPath, $footerSource, true);
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
			$this->readSourceContent($fullPath, $source);
		}
	}

	return implode("\n", [
		$headerSource,
		$source,
		$footerSource,
	]);

	throw new NotFoundException(implode("/", [$path, $filename]));
}

/**
 * If $out variable is empty, reads the source file provided, transforming
 * where necessary, providing $out with the HTML source.
 *
 * @param string $fullPath Absolute path to source file on disk
 * @param string &$out Reference to variable to write HTML source to
 * @param boolean $replaceOut When true, $out's contents will be replaced. When
 * false, if $out has contents already it will be ignored
 */
private function readSourceContent($fullPath, &$out, $replaceOut = false) {
	if($replaceOut
	&& !empty($out)) {
		return;
	}

	$source = file_get_contents($fullPath);
	$extension = pathinfo($fullPath, PATHINFO_EXTENSION);
	if(strpos($extension, "htm") !== 0) {
		$source = Transformer::toHtml($source, $extension);
	}

	$out = $source;
}

/**
 *
 */
public function createResponseContent($html, $config) {
	if(!is_string($html)) {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException(
			gettype($html) . " is not a string");
	}
	$domDocument = new \Gt\Dom\Document($html, $config);

	return $domDocument;
}


}#