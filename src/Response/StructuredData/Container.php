<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\StructuredData;
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