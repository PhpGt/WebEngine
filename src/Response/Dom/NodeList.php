<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response\Dom;

class NodeList implements \ArrayAccess, \Iterator {

private $iteratorIndex;
public $nodeArray;

public function __construct($domNodeList) {
	$this->nodeArray = [];

	for($i = 0, $len = $domNodeList->length; $i < $len; $i++) {
		$node = $domNodeList->item($i);

		// TODO: Create Node, add to array.
		// Then implement Iterator and ArrayAccess.
	}
}

// Iterator --------------------------------------------------------------------
/**
 *
 */
public function rewind() {
	$this->iteratorIndex = 0;
}

/**
 *
 */
public function valid() {
	return isset($this->_elArray[$this->iteratorIndex]);
}

/**
 *
 */
public function current() {
	return $this->_elArray[$this->iteratorIndex];
}

/**
 *
 */
public function key() {
	return $this->iteratorIndex;
}

/**
 *
 */
public function next() {
	++$this->iteratorIndex;
}

}#