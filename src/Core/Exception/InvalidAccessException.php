<?php
/**
 * Used when an area of code is accessed that shouldn't be, such as in the
 * offsetSet method of a class that implements ArrayAccess, but wishes to
 * remain read-only.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core\Exception;

class InvalidAccessException extends GtException {}#