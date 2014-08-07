<?php
/**
 * TODO: Extend from an HTTP Error exception.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

class NotFoundException extends \Gt\Core\Exception\GtException {

public function __construct() {
	http_response_code(404);
	call_user_func_array([parent, "construct"], func_get_args());
}

}#