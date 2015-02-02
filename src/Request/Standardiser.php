<?php
/**
 * Used to standardise URIs across applications, according to the configuration
 * of that application.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

use Gt\Core\ConfigObj;

class Standardiser {

/**
 * Takes a URL and fixes it according to the configuration properties
 * @param string $uri The request URI
 * @param ConfigObj $config Object containing request configuration properties
 *
 * @return string The new URI, standardised to configuration options
 */
public function fixUri($uri, ConfigObj $config) {
	$queryString = parse_url($uri, PHP_URL_QUERY);
	$fixed = strtok($uri, "?");

	$pathinfo = pathinfo($fixed);
	$file = $pathinfo["filename"];
	$ext  = empty($pathinfo["extension"])
		? null
		: $pathinfo["extension"];

	$fixed = $this->fixHtmlExtension($fixed, $file, $ext, $config);
	$fixed = $this->fixIndexFilename($fixed, $file, $ext, $config);
	$fixed = $this->fixTrailingSlash($fixed, $ext, $config);
	$fixed = $this->fixTrailingExtSlash($fixed, $ext);

	if($uri !== $fixed
	&& !empty($queryString)) {
		$fixed .= "?" . $queryString;
	}

	return $fixed;
}

/**
 * If force_extension configuration value is true, requests to
 * directories have .html appended to them.
 *
 * @param string $uri The request URI
 * @param string $file The requested file name, with no path.
 * @param string $ext The requested file extension, or null.
 * @param ConfigObj $config The provided configuration options object.
 *
 * @return string The fixed URI.
 */
public function fixHtmlExtension($uri, $file, $ext, ConfigObj $config) {
	if(!isset($config->force_extension)) {
		return $uri;
	}

	$lastChar = substr($uri, -1);

	if($config->force_extension) {
		if(empty($ext) && !empty($file)) {
			if($lastChar === "/") {
				$uri = substr($uri, 0, -1)
					. ".html";
			}
			else {
				$uri .= ".html";
			}
		}
	}
	else if($ext === "html") {
		$uri = substr($uri, 0, strrpos($uri, ".html"));
	}

	return $uri;
}

/**
 * Ensures that 1) when index_force configuration option is ture, and a
 * directory is requested that the URI is changed to the index_filename
 * configuration option, and 2) when index_force configuration option is false,
 * and the index_filename configuration option is requested, the URI is changed
 * to a directory style URI.
 *
 * @param string $uri The request URI
 * @param string $file The requested file name, with no path.
 * @param string $ext The requested file extension, or null.
 * @param ConfigObj $config The provided configuration options object.
 *
 * @return string The fixed URI.
 */
public function fixIndexFilename($uri, $file, $ext, ConfigObj $config) {
	if(!isset($config->index_force)
	|| !isset($config->index_filename)) {
		return $uri;
	}

	$lastChar = substr($uri, -1);

	if(empty($file)) {
		if($config->index_force) {
			$uri .= $config->index_filename;
		}
	}
	else if($file === $config->index_filename
	&& (empty($ext) || $ext === "html")) {
		if(!$config->index_force) {
			// Handle URIs with an extension that also have trailing slash.
			if($lastChar === "/") {
				$uri = substr($uri, 0, -1);
			}
			$uri = substr($uri, 0, strrpos($uri, "/") + 1);
		}
	}

	return $uri;
}

/**
 * Ensures that for extension-less URIs, the trailing slash is forced on or off,
 * depending on the value of pageview_trailing_directory_slash configuration
 * option.
 *
 * Also, removes any trailing slashes from an extension-full URI.
 *
 * @param string $uri The request URI
 * @param string $ext The requested file extension, or null.
 * @param ConfigObj $config The provided configuration options object.
 *
 * @return string The fixed URI.
 */
public function fixTrailingSlash($uri, $ext, ConfigObj $config) {
	if(!isset($config->pageview_trailing_directory_slash)) {
		return $uri;
	}

	$lastChar = substr($uri, -1);

	if(empty($ext)) {
		if($config->pageview_trailing_directory_slash) {
			if($lastChar !== "/") {
				$uri .= "/";
			}
		}
		else {
			if($lastChar === "/"
			&& $uri !== "/") {
				$uri = substr($uri, 0, -1);
			}
		}
	}

	return $uri;
}

/**
 * Ensures that request URIs made to files with extensions do not have a
 * trailing slash, independent to pageview_trailing_directory_slash.
 * URIs with file extensions should not end with a slash, ever.
 *
 * @param string $uri The request URI
 * @param string $ext The requested file extension, or null.
 *
 * @return string The fixed URI.
 */
public function fixTrailingExtSlash($uri, $ext) {
	$lastChar = substr($uri, -1);
	if(!empty($ext) && $lastChar === "/") {
		$uri = substr($uri, 0, strrpos($uri, "/"));
	}

	return $uri;
}

}#