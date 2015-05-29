<?php
/**
 * TODO: Extend from an HTTP Error exception.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Response;

class NotFoundException extends \Gt\Core\Exception\GtException {

public function __construct() {
	call_user_func_array([$this, "parent::" . __FUNCTION__], func_get_args());
}

}#
