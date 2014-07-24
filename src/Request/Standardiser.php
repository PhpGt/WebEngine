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
use Gt\Core\Obj;

class Standardiser {

/**
 * Takes a URL and fixes it according to the configuration properties
 * @param string $uri The request URI
 * @param Obj $config Object containing request configuration properties
 * @return string The new URI, standardised to configuration options
 */
public function fixUri($uri, Obj $config) {
	$pathinfo = pathinfo($uri);
	$file = strtok($pathinfo["filename"], "?");
	$ext  = empty($pathinfo["extension"])
		? null
		: strtok($pathinfo["extension"], "?");

	// Fix index filename:
	if(empty($file)) {
		if($config->index_force) {
			$uri .= $config->index_filename;			
		}
	}
	else if($file === $config->index_filename) {
		if(!$config->index_force) {
			$uri = substr($uri, 0, strrpos($uri, "/"));
		}
	}

	// Fix html extension:
	if(isset($config->pageview_html_extension)) {
		if($config->pageview_html_extension) {
			if(empty($ext)) {
				$uri .= ".html";
			}
		}
		else if(strpos($ext, "htm") === 0) {
			$uri = substr($uri, 0, strrpos($uri, ".htm"));
		}
	}

	// Fix trailing slash:
	if(isset($config->pageview_trailing_directory_slash)) {
		$firstChar = substr($uri, 0, 1);
		$lastChar = substr($uri, -1);
		if($config->pageview_trailing_directory_slash) {
			if(empty($ext) && $lastChar !== "/") {
				$uri .= "/";
			}
		}
		else {
			if(strlen($uri) > 1 && $lastChar === "/") {
				$uri = substr($uri, 0, strrpos($uri, "/"));
			}
		}
	}

	// Strip trailing slashes if there is an extension.
	// We don't want to see /dir/page.html/
	$lastChar = substr($uri, -1);
	if(!empty($ext) && $lastChar === "/") {
		$uri = substr($uri, 0, strrpos($uri, "/"));
	}

	return $uri;
}

}#