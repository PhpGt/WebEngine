<?php
/**
 * Abstraction class to map directly to external logging framework, so the
 * choice in Logger can be changed at a later date.
 *
 * PSR-3 compliant, meaning the implementation must adhere to the interface
 * Psr\Log\LoggerInterface.
 *
 * Available log levels (in order of highest priority to lowest):
 * LogLevel::EMERGENCY;
 * LogLevel::ALERT;
 * LogLevel::CRITICAL;
 * LogLevel::ERROR;
 * LogLevel::WARNING;
 * LogLevel::NOTICE;
 * LogLevel::INFO;
 * LogLevel::DEBUG;
 *
 * Currently using Katzgrau's Klogger.
 * https://github.com/katzgrau/KLogger
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends \Katzgrau\KLogger\Logger {

public function __construct($path = null, $level = LogLevel::DEBUG) {
	if(is_null($path)) {
		$path = Path::get(Path::ROOT);
	}

	parent::__construct($path, $level);
}

}#