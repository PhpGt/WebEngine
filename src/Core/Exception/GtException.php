<?php
/**
 * Base exception for use within PHP.Gt.
 * 
 * TODO: Depending on application debug configuration, will display 500 error 
 * and stack trace, or generic error page.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core\Exception;

class GtException extends \Exception {}#