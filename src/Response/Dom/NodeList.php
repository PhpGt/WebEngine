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

	if($domNodeList instanceof \DOMNodeList) {
		for($i = 0, $len = $domNodeList->length; $i < $len; $i++) {
			$node = new Node($domNodeList->item($i));
			$this->nodeArray []= $node;
		}
	}
	else if(is_array($domNodeList)) {
		for($i = 0, $len = count($domNodeList); $i < $len; $i++) {
			$node = new Node($domNodeList[$i]);
			$this->nodeArray []= $node;
		}
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