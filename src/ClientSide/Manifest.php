<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
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