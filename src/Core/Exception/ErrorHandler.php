<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core\Exception;

class ErrorHandler {

public static function throwErrorException($number, $msg, $file, $line) {
	throw new GtErrorException($msg, 0, $number, $file, $line);
}

}#