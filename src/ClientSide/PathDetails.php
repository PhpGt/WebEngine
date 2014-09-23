<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Dom\NodeList;

class PathDetails {

// TODO: Wrap in Iteratable:
public $nodeList;

/**
 * @param NodeList $domNodeList List of elements to represent
 */
public function __construct($nodeList = []) {
	$this->nodeList = $nodeList;
}

}#