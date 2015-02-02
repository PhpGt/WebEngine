<?php
/**
 * Thrown if a key is requested from the Session that does not exist.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Session;

class SessionStoreNotFoundException extends \Gt\Core\Exception\GtException {}#