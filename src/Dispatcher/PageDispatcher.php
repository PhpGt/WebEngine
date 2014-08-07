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

class PageDispatcher extends Dispatcher {

public function getPath($uri) {
	$pageViewDir = Path::fixCase(Path::get(Path::PAGEVIEW) . $uri);
	if(!is_dir($pageViewDir)) {
		$pageViewDir = dirname($pageViewDir);
	}

	return $pageViewDir;
}

public function loadSource($path, $filename) {
	// Only load .html files (for now).
	var_dump(func_get_args());die();
}

public function createResponseContent($html) {
	$domDocument = new \Gt\Response\Dom\Document($html);

	return $domDocument;
}


}#