<?php
/**
 * Abstraction class to map directly to external logging framework, so the
 * choice in Logger can be changed at a later date.
 *
 * Exposes error, warning, info, debug, etc. as public methods.
 *
 * Currently using Katzgrau's Klogger.
 * https://github.com/katzgrau/KLogger
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

class Logger extends \Katzgrau\KLogger\Logger {

public function __construct($path = null) {
	if(is_null($path)) {
		$path = Path::get(Path::ROOT);
	}

	parent::__construct($path);
}

}#