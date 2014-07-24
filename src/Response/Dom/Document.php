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
	$this->domDocument = new \DOMDocument("1.0", "utf-8");
}

public function __toString() {
	return $this->domDocument->saveHTML();
}

/**
 * Allows unserialization of one or more HTML files.
 * @param string|array $content A string of raw-HTML, or an array of strings 
 * containing raw-HTML to concatenate and unserialize.
 */
public function load($content = "<!doctype html>") {
	$string = "";

	if(!is_array($content)) {
		$content = [$content];
	}

	foreach ($content as $c) {
		$string .= $c . PHP_EOL;
	}
	
	libxml_use_internal_errors(true);

	$string = mb_convert_encoding(trim($string), "HTML-ENTITIES", "utf-8");
	$this->domDocument->loadHTML($string, 
		0
		| LIBXML_COMPACT
		| LIBXML_HTML_NOIMPLIED
		| LIBXML_NOBLANKS
		| LIBXML_NOXMLDECL
		| LIBXML_NSCLEAN
		| LIBXML_PARSEHUGE
	);
}

}#