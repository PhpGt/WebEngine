<?php
/**
 * Thrown if a key is requested from the Session that does not exist.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Session;

class SessionStoreNotFoundException extends \Gt\Core\Exception\GtException {}#