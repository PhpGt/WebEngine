<?php
/**
 *
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Path {

const DATABASE		= "DATABASE";
const PAGE			= "PAGE";
const PAGECODE		= "PAGECODE";
const PAGETOOL		= "PAGETOOL";
const PAGEVIEW		= "PAGEVIEW";
const PUBLICFILES	= "PUBLICFILES";
const ROOT			= "ROOT";
const SCRIPT		= "SCRIPT";
const API			= "API";
const APICODE		= "APICODE";
const APITOOL		= "APITOOL";
const APIVIEW		= "APIVIEW";
const SRC			= "SRC";
const STYLE			= "STYLE";
const WWW			= "WWW";
const GTROOT		= "GTROOT";

/**
 * Returns the absolute path on disk to the requested path constant, while
 * fixing the path's case, so your application does not need to know if the
 * developer is using lower case, upper case, camel case, etc.
 *
 * @param string $name One of this class's constants.
 *
 * @return string The absolute path on disk.
 */
public static function get($name) {
	$p = null;

	switch($name) {
	case self::DATABASE:
		$p = self::get(self::SRC) . "/Database";
		break;

	case self::PAGE:
		$p = self::get(self::SRC) . "/Page";
		break;

	case self::PAGECODE:
		$p = self::get(self::PAGE) . "/Code";
		break;

	case self::PAGETOOL:
		$p = self::get(self::PAGE) . "/Tool";
		break;

	case self::PAGEVIEW:
		$p = self::get(self::PAGE) . "/View";
		break;

	case self::PUBLICFILES:
		$p = self::get(self::SRC) . "/PublicFiles";
		break;

	case self::ROOT:
		$p = dirname($_SERVER["DOCUMENT_ROOT"]);
		break;

	case self::SCRIPT:
		$p = self::get(self::SRC) . "/Script";
		break;

	case self::API:
		$p = self::get(self::SRC) . "/API";
		break;

	case self::APICODE:
		$p = self::get(self::API) . "/Code";
		break;

	case self::APITOOL:
		$p = self::get(self::API) . "/Tool";
		break;

	case self::APIVIEW:
		$p = self::get(self::API) . "/View";
		break;

	case self::SRC:
		$p = self::get(self::ROOT) . "/src";
		break;

	case self::STYLE:
		$p = self::get(self::SRC) . "/Style";
		break;

	case self::WWW:
		$p = self::get(self::ROOT) . "/www";
		break;

	case self::GTROOT:
		$p = realpath(__DIR__ . "/../../");
		break;

	default:
		throw new \UnexpectedValueException("Invalid path: $name");
	}

	return self::fixCase($p);
}

/**
 * Takes an absolute path and checks the case for each directory in the tree,
 * correcting it according to what is actually stored on disk.
 *
 * @param string $path Original path
 * @param bool $uriPath Defaults to false. Set to true to treat the path as a
 * uri, prefixing with the PAGEVIEW path automatically in order to use as
 * an absolute path.
 *
 * @return string Correctly-cased path. Returns in URI style if $uriPath is set
 * to true.
 */
public static function fixCase($path, $uriPath = false) {
	if($uriPath) {
		$path = self::get(self::PAGEVIEW) . $path;
	}

	$pathArray = explode("/", $path);

	$currentPath = "";
	foreach ($pathArray as $i => $p) {
		if(!file_exists($currentPath . "/" . $p)) {
			if(!is_dir($currentPath)) {
				continue;
			}

			foreach (new \DirectoryIterator($currentPath) as $fileInfo) {
				if($fileInfo->isDot()) {
					continue;
				}

				$filename = $fileInfo->getFilename();

				if(strcasecmp($filename, $p) === 0) {
					$pathArray[$i] = $filename;
				}
				else if(strcasecmp($filename, $p . ".html") === 0) {
					$pathArray[$i] = $filename;
				}
			}
		}
		$currentPath .= $pathArray[$i] . "/";
	}

	$result = implode("/", $pathArray);

	if($uriPath) {
		$result = substr($result, strlen(self::get(self::PAGEVIEW)) );
	}

	return $result;
}

}#