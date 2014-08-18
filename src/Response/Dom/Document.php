<?php
/**
 * TODO: Docs
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\Dom;

use \Gt\Response\ResponseContent;

class Document extends ResponseContent {

const DEFAULT_HTML = "<!doctype html>";
private $domDocument;

/**
 * Passing in the HTML to parse as an optional first parameter automatically
 * calls the load function with provided HTML content.
 */
public function __construct($html = null) {
	$this->domDocument = new \DOMDocument("1.0", "utf-8");

	if(!is_null($html)) {
		if(empty($html)) {
			$html = self::DEFAULT_HTML;
		}
		$this->load($html);
	}
}

public function __toString() {
	return $this->domDocument->saveHTML();
}

/**
 * Allows unserialization of one or more HTML files.
 * @param string|array $content A string of raw-HTML, or an array of strings
 * containing raw-HTML to concatenate and unserialize.
 */
public function load($content = null) {
	$string = "";

	if(is_null($content)) {
		$content = self::DEFAULT_HTML;
	}

	if(!is_array($content)) {
		$content = [$content];
	}

	foreach ($content as $c) {
		$string .= $c . PHP_EOL;
	}

	libxml_use_internal_errors(true);

	$string = mb_convert_encoding(trim($string), "HTML-ENTITIES", "utf-8");
	$this->domDocument->loadHTML($string);
}

}#