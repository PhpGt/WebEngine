<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

abstract class Manifest {

/**
 * @return string MD5 hash representation
 */
abstract public function calculateFingerprint($details);

/**
 * @return bool True if valid, false if invalid
 */
abstract public function checkValid();

/**
 * @return void
 */
abstract public function expand();

}#