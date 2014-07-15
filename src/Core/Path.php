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

const CONFIG		= "CONFIG";
const DATABASE		= "DATABASE";
const PAGE			= "PAGE";
const PAGECODE		= "PAGECODE";
const PAGETOOL		= "PAGETOOL";
const PAGEVIEW		= "PAGEVIEW";
const PUBLICFILES	= "PUBLICFILES";
const ROOT			= "ROOT";
const SCRIPT		= "SCRIPT";
const SERVICE		= "SERVICE";
const SERVICECODE	= "SERVICECODE";
const SERVICETOOL	= "SERVICETOOL";
const SERVICEVIEW	= "SERVICEVIEW";
const SRC			= "SRC";
const STYLE			= "STYLE";
const WWW			= "WWW";

	switch($name) {
	case "config":
		return self::root . "/cfg";
		break;

	case "database":
		return self::src . "/Database";
		break;

	case "page":
		return self::src . "/Page";
		break;

	case "pagecode":
		return self::page . "/Code";
		break;

	case "pagetool":
		return self::page . "/Tool";
		break;

	case "pageview":
		return self::page . "/View";
		break;

	case "publicfiles":
		return self::src . "/PublicFiles";
		break;

	case "root":
		return dirname($_SERVER["DOCUMENT_ROOT"]);
		break;

	case "script":
		return self::src . "/Script";
		break;

	case "service":
		return self::src . "/Service";
		break;

	case "servicecode":
		return self::service . "/Code";
		break;

	case "servicetool":
		return self::service . "/Tool";
		break;

	case "serviceview":
		return self::service . "/View";
		break;

	case "src":
		return self::root . "/src";
		break;

	case "style":
		return self::src . "/Style";
		break;

	case "www":
		return self::root . "/www";
		break;
	}
}

}#