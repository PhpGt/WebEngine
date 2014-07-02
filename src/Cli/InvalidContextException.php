<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class InvalidContextException extends \Gt\Core\Exception\GtException {

public function __construct($context) {
	$this->message = "Script can not be invoked in context '$context'";
}

}#