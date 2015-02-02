<?php
/**
 * A convenient alternative to accessing an element's list of tokens as a
 * space-delimited attribute string.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dom;

class TokenList {

private $list;

public function __construct($node, $attribute = "class", $separator = " ") {
	$this->list = explode($separator, $node->getAttribute($attribute));
}

/**
 * Returns an item in the list by its index (or null if the number is
 * greater than or equal to the length of the list)
 *
 * @param int $index Zero-based index of token in list
 *
 * @return string|null If set, the string token at the supplied index. If there
 * is no token at that index, returns null
 */
public function item($index) {
	if(isset($list[$index])) {
		return $list[$index];
	}

	return null;
}

/**
 * Returns true if the underlying string contains token, otherwise false.
 *
 * @param string $token The token to search for
 *
 * @return bool true if the underlying string contains token, otherwise false
 */
public function contains($token) {
	return in_array($token, $this->list);
}

/**
 * Adds token to the underlying string. If token is already part of the
 * underlying string, ignore.
 *
 * @param string $token The token to add
 *
 * @return string The added token
 */
public function add($token) {
	if(!$this->contains($token)) {
		$this->list []= $token;
	}

	$this->rebuildAttribute();
	return $token;
}

public function remove($token) {

}

public function toggle($token) {

}

}#