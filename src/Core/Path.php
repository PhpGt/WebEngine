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

public static function get($name) {

	switch($name) {
	case self::DATABASE:
		return self::get(self::SRC) . "/Database";

	case self::PAGE:
		return self::get(self::SRC) . "/Page";

	case self::PAGECODE:
		return self::get(self::PAGE) . "/Code";

	case self::PAGETOOL:
		return self::get(self::PAGE) . "/Tool";

	case self::PAGEVIEW:
		return self::get(self::PAGE) . "/View";

	case self::PUBLICFILES:
		return self::get(self::SRC) . "/PublicFiles";

	case self::ROOT:
		return dirname($_SERVER["DOCUMENT_ROOT"]);

	case self::SCRIPT:
		return self::get(self::SRC) . "/Script";

	case self::API:
		return self::get(self::SRC) . "/API";

	case self::APICODE:
		return self::get(self::API) . "/Code";

	case self::APITOOL:
		return self::get(self::API) . "/Tool";

	case self::APIVIEW:
		return self::get(self::API) . "/View";

	case self::SRC:
		return self::get(self::ROOT) . "/src";

	case self::STYLE:
		return self::get(self::SRC) . "/Style";

	case self::WWW:
		return self::get(self::ROOT) . "/www";

	case self::GTROOT:
		return realpath(__DIR__ . "/../../");

	default:
		throw new \UnexpectedValueException("Invalid path: $name");
	}
}

}#