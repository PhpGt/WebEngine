<?php
/**
 * Used when an area of code is accessed that shouldn't be, such as in the
 * offsetSet method of a class that implements ArrayAccess, but wishes to
 * remain read-only.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core\Exception;

class InvalidAccessException extends GtException {}#