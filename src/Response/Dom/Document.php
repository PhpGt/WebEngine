<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\Dom;
use \Gt\Response\ResponseContent;

class Document extends ResponseContent {

private $domDocument;

public function __construct() {
	$this->domDocument = new DOMDocument("1.0", "utf-8");
}

public function load($response) {
	libxml_use_internal_errors(true);

	$html = "<!doctype html>";
	// TODO: Actually load the HTML.
	$html = mb_convert_encoding($html, "HTML-ENTITIES", "utf-8");
}

}#