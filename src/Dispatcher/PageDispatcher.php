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

public function createResponseContent($html) {
	$domDocument = new \Gt\Response\Dom\Document($html);

	return $domDocument;
}

/**
 * From the Requested URI, .... TODO: Docs.
 */
public function getPath($uri) {
	$pageViewDir = Path::fixCase(Path::get(Path::PAGEVIEW) . $uri, true);
	var_dump($pageViewDir);die();
}

public function loadSource($path) {

}

}#