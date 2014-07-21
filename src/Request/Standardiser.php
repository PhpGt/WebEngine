<?php
/**
 * Used to standardise URIs across applications, according to the configuration
 * of that application. 
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;
use \Gt\Response\Redirect as Redirect;

class Standardiser {

/**
 * Takes a URL and fixes it according to the configuration properties.
 * @param string $uri The request URI
 * @param Obj $config Object containing configuration properties.
 * @return string The new URI, standardised to configuration options.
 */
public function fixUri($uri, $config) {
	$ext = pathinfo($uri, PATHINFO_EXTENSION);

	if($config->pageview_html_extension) {
		if(empty($ext)) {
			$uri .= ".html";
		}
	}
	else if(strpos($ext, "htm") === 0) {
		$uri = substr($uri, 0, strrpos($uri, ".htm"));
	}

	$firstChar = substr($uri, 0, 1);
	$lastChar = substr($uri, -1);
	if($config->pageview_trailing_slash) {
		if($lastChar !== "/") {
			$uri .= "/";
		}
	}
	else {
		if(strlen($uri) > 1 && $lastChar === "/") {
			$uri = substr($uri, 0, strrpos($uri, "/"));
		}
	}

	return $uri;
}

}#