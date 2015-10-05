<?php
/**
 * TODO: Docs
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

use \Gt\Core\Path;
use \Gt\Response\NotFoundException;
use \Gt\Page\Transformer;
use \Gt\Response\ResponseContent;

class PageDispatcher extends Dispatcher {

protected static $validExtensions = [
	"html",
	Transformer::TYPE_MARKDOWN,
];

/**
 * Returns the upper-most directory available to the type of dispatcher used,
 * for instance src/Page or src/Api.
 *
 * @return string Absolute path of directory
 */
public function getBasePath() {
	return Path::get(Path::PAGE);
}

/**
 * Gets the absolute file path of a source file according to the provided URI,
 * and updates the fixedUri parameter accordingly.
 *
 * @param string $uri The URI as requested by the browser
 * @param string $fixedUri The URI after it has been adjusted
 * @param bool $throw Set to false to suppress throwing NotFoundExceptions
 *
 * @return string The absolute file path on disk of the source file
 */
public function getPath($uri, &$fixedUri, $throw = true) {
	$basePath = $this->getBasePath();
	$pageDir = Path::fixCase($basePath . $uri);
	$fixedUri = Path::fixCase($basePath . $uri, $basePath);

	if(!is_dir($pageDir)) {
		$pageDir_container = dirname($pageDir);

		if(is_dir($pageDir_container)) {
			if(!file_exists($basePath . $fixedUri)) {
				if(!$throw) {
					return $uri;
				}

				throw new NotFoundException($fixedUri);
			}
		}
		else {
			if(!$throw) {
				return $uri;
			}

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
	$pathFileBase = pathinfo($filename, PATHINFO_FILENAME);

	// Look for a header and footer view file up the tree.
	$headerFooterPathTop = Path::get(Path::PAGE);
	$headerFooterPath = $path;

	do {
		if(is_dir($headerFooterPath)) {
			foreach (new \DirectoryIterator($headerFooterPath) as $fileInfo) {
				if($fileInfo->isDot()) {
					continue;
				}

				$headerFooterFilename = $fileInfo->getFilename();
				if($headerFooterFilename[0] !== "_") {
					continue;
				}

				$fileBase = strtok($headerFooterFilename, ".");
				$specialName = substr(strtolower($fileBase), 1);
				$fullPath = implode("/", [
					$headerFooterPath,
					$headerFooterFilename
				]);

				switch($specialName) {
				case "header":
					$this->readSourceContent($fullPath, $headerSource, true);
					break;

				case "footer":
					$this->readSourceContent($fullPath, $footerSource, true);
					break;
				}
			}
		}

		// Go up a directory...
		$headerFooterPath = substr(
			$headerFooterPath,
			0,
			strrpos($headerFooterPath, "/")
		);
		// ... until we are above the Page View directory.
	} while (strstr($headerFooterPath, $headerFooterPathTop));

	foreach (new \DirectoryIterator($path) as $fileInfo) {
		if($fileInfo->isDot()) {
			continue;
		}

		$pageFilename = $fileInfo->getFilename();
		$fileBase = strtok($pageFilename, ".");
		$extension = $fileInfo->getExtension();
		if(!in_array(strtolower($extension), self::$validExtensions)) {
			// Only include the current file's source if its extension is
			// within the validExtensions array.
			continue;
		}

		if(strcasecmp($fileBase, $pathFileBase) === 0) {
			$fullPath = implode("/", [$path, $pageFilename]);
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
 * From given file path, return the serialised content of an error page for the
 * provided response code.
 *
 * @param string $path The abolute path on disk to the requested source
 * directory
 * @param string $filename The requested base filename, with extension
 *
 * @return mixel The full, raw source of the error response
 */
public function loadError($path, $filename, $responseCode) {
	// TODO: Handle error pages properly!
	return "ERROR $responseCode";
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

/**
 *
 */
public function setContentUri($uri, ResponseContent $content) {
	if(strrpos($uri, "/") === strlen($uri) - 1) {
		$uri .= "index";
	}

	$body = $content->querySelector("body");
	$uri = trim($uri, "/");
	$classArray = [];

	foreach(explode("/", $uri) as $uriPart) {
		$newClass = "";

		foreach($classArray as $c) {
			if(strlen($newClass) > 0) {
				$newClass .= "_";
			}
			$newClass .= $c;
		}

		if(strlen($newClass) > 0) {
			$newClass .= "_";
		}

		$classArray []= $newClass . $uriPart;
	}

	foreach($classArray as $c) {
		$body->classList->add("dir--" . $c);
	}

	$uri = str_replace("/", "_", $uri);
	$body->id = "uri--" . $uri;

}

}#
