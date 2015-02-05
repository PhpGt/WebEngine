<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\StructuredData;

use \Gt\Response\ResponseContent;

class Container extends ResponseContent {

private $baseComponent;

public function __construct() {
	$this->baseComponent = new Component();
}

public function __toString() {
	// TODO: Need to know what to encode to.
	return json_encode($this->baseComponent);
}

}#