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

const CONFIG		= 100;
const DATABASE		= 200;
const PAGE			= 300;
const PAGECODE		= 400;
const PAGETOOL		= 500;
const PAGEVIEW		= 600;
const PUBLICFILES	= 700;
const ROOT			= 800;
const SCRIPT		= 900;
const SERVICE		= 1000;
const SERVICECODE	= 1100;
const SERVICETOOL	= 1200;
const SERVICEVIEW	= 1300;
const SRC			= 1400;
const STYLE			= 1500;
const WWW			= 1600;

public static function get($path) {
	switch ($path) {
	case self::CONFIG:
		return self::get(self::ROOT) . "/cfg";
		break;

	case self::DATABASE:
		return self::get(self::SRC) . "/Database";
		break;

	case self::PAGE:
		return self::get(self::SRC) . "/Page";
		break;

	case self::PAGECODE:
		return self::get(self::PAGE) . "/Code";
		break;

	case self::PAGETOOL:
		return self::get(self::PAGE) . "/Tool";
		break;

	case self::PAGEVIEW:
		return self::get(self::PAGE) . "/View";
		break;

	case self::PUBLICFILES:
		return self::get(self::SRC) . "/PublicFiles";
		break;

	case self::ROOT:
		return dirname($_SERVER["DOCUMENT_ROOT"]);
		break;

	case self::SCRIPT:
		return self::get(self::SRC) . "/Script";
		break;

	case self::SERVICE:
		return self::get(self::SRC) . "/Service";
		break;

	case self::SERVICECODE:
		return self::get(self::SERVICE) . "/Code";
		break;

	case self::SERVICETOOL:
		return self::get(self::SERVICE) . "/Tool";
		break;

	case self::SERVICEVIEW:
		return self::get(self::SERVICE) . "/View";
		break;

	case self::SRC:
		return self::get(self::ROOT) . "/src";
		break;

	case self::STYLE:
		return self::get(self::SRC) . "/Style";
		break;

	case self::WWW:
		return self::get(self::ROOT) . "/www";
		break;

	default:
		// This case is required when the provided $path parameter is not a 
		// class constant.
		throw new \UnexpectedValueException();
		break;
	}
}

}#