<?php
/**
 *
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

class Path {

const DATA			= "data";
const PAGE			= "page";
const ROOT			= "root";
const ASSET			= "asset";
const SCRIPT		= "script";
const STYLE			= "style";
const API			= "api";
const APILOGIC		= "apilogic";
const APITOOL		= "apitool";
const APIVIEW		= "apiview";
const SRC			= "src";
const WWW			= "www";
const GTROOT		= "gtroot";

private static $configArray = [];

/**
 * Store a private reference to the provided configuration object for use when
 * building paths later on.
 *
 * @param ConfigObj $config The config object to be stored in the internal
 * private array
 *
 * @return void
 */
public static function setConfig($config) {
	$configName = $config->getName();
	self::$configArray[$configName] = $config;
}

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
	switch($name) {
	case self::DATA:
		$p = self::get(self::ROOT) . "/data";
		break;

	case self::PAGE:
		$p = self::get(self::SRC) . "/Page";
		break;

	case self::ROOT:
		if(empty($_SERVER["DOCUMENT_ROOT"])) {
			$p = getcwd();
		}
		else {
			$p = dirname($_SERVER["DOCUMENT_ROOT"]);
		}
		break;

	case self::SCRIPT:
		$p = self::get(self::SRC) . "/Script";
		break;

	case self::API:
		$apiName = self::$configArray["api"]->api_directory;
		$p = self::get(self::SRC) . "/$apiName";
		break;

	case self::APILOGIC:
		$p = self::get(self::API) . "/Logic";
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

	case self::ASSET:
		$p = self::get(self::SRC) . "/Asset";
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
 * @param boolean|string $stripPrefix Defaults to false. Set to non-false string
 * to treat the returned path as a uri, removing the provided urlPath prefix
 * automatically in order to use as an absolute URI.
 * @param boolean|string $stripSuffix Defaults to false. Set to non-false string
 * to automatically remove the provided string ending.
 *
 * @return string Correctly-cased path. Returns in URI style if $stripPrefix
 * is set to a non-null string.
 */
public static function fixCase($path,
$stripPrefix = false, $stripSuffix = false) {
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

				$fileName = $fileInfo->getFilename();

				if(strcasecmp($fileName, $p) === 0) {
					$pathArray[$i] = $fileName;
				}
				else if(strcasecmp($fileName, $p . ".html") === 0) {
					$pathArray[$i] = $fileName;
				}
			}
		}
		$currentPath .= $pathArray[$i] . "/";
	}

	$result = implode("/", $pathArray);

	if(is_string($stripPrefix)) {
		if(strpos($result, $stripPrefix) === 0) {
			$result = substr($result, strlen($stripPrefix) );
		}
	}
	if(is_string($stripSuffix)) {
		if(substr($result, -strlen($stripSuffix)) === $stripSuffix) {
			$result = substr($result, 0, -strlen($stripSuffix));
		}
	}

	return $result;
}

/**
 * From a given PHP script, return the correct fully qualified namespace from
 * its location on disc, according to PSR-4.
 *
 * @param string $path Absolute path to PHP script
 *
 * @return string|bool A string containing the fully qualified namespace, or
 * false if file cannot be found
 */
public static function getNamespace($path) {
	$path = self::fixCase($path);

	if(!file_exists($path)) {
		return false;
	}

	$src = self::get(self::SRC);
	if(strpos($path, $src) !== 0) {
		return false;
	}

	$subPath = substr($path, strlen($src));
	$pathInfo = pathinfo($subPath);

	if(!empty($pathInfo["dirname"])
	&& !empty($pathInfo["filename"])) {
		$path = $pathInfo["dirname"];
		$path .= "/" . $pathInfo["filename"];

		return "\\" . APP_NAMESPACE . str_replace("/", "\\", $path);
	}

	return false;
}

}#