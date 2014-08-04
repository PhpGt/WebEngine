<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;
class PageDispatcher extends Dispatcher {

public function createResponseContent() {
	$domDocument = new \Gt\Response\Dom\Document();

	// TODO: Find the paths, something like the following?
	// $htmlPath = $this->getPath(Dispatcher::PATH_HTML);

	$domDocument->load();

	return $domDocument;
}

}#