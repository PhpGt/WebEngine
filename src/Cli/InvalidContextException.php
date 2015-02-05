<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Cli;

class InvalidContextException extends \Gt\Core\Exception\GtException {

public function __construct($context) {
	$this->message = "Script can not be invoked in context '$context'";
}

}#