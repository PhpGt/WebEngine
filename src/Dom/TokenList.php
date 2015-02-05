<?php
/**
 * A convenient alternative to accessing an element's list of tokens as a
 * space-delimited attribute string.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

class TokenList {

private $list;
private $node;
private $attributeName;
private $attributeValue;
private $separator;

public function __construct($node, $attributeName = "class", $separator = " ") {
	$this->node = $node;
	$this->attributeName = $attributeName;
	$this->separator = $separator;
	$this->refreshAttribute();
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
	$this->rebuildAttribute();

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
	$this->rebuildAttribute();

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
	$this->rebuildAttribute();

	if(!$this->contains($token)) {
		$this->list []= $token;
	}

	$this->rebuildAttribute();
	return $token;
}

/**
 * Removes token from the underlying string. If token is not part of the
 * underlying string, ignore.
 *
 * @param string $token The token to remove
 *
 * @return string The removed token
 */
public function remove($token) {
	$this->rebuildAttribute();

	if($this->contains($token)) {
		$index = array_search($token, $this->list);
		unset($this->list[$index]);
		$this->list = array_values($this->list);
	}

	$this->rebuildAttribute();
	return $token;
}

/**
 * Removes token from string and returns false. If token doesn't exist it's
 * added and the function returns true.
 *
 * @param string $token The token to toggle
 *
 * @return book True if the token is added, false if the token is removed
 */
public function toggle($token) {
	$this->rebuildAttribute();

	if($this->contains($token)) {
		$this->remove($token);
		return false;
	}
	else {
		$this->add($token);
		return true;
	}
}

/**
 * From the node's actual attribute value, refresh the underlying properties.
 *
 * @return void
 */
private function refreshAttribute() {
	$attributeValue = "";
	if($this->node->hasAttribute($this->attributeName)) {
		$attributeValue = $this->node->getAttribute($this->attributeName);
	}

	$this->list = explode($this->separator, $attributeValue);
	$this->attributeValue = $attributeValue;
}

/**
 * From the underlying list in its given state, rebuild the attribute
 * it represents by removing, then re-adding each token separately.
 *
 * @return void
 */
private function rebuildAttribute() {
	$currentAttributeValue = $this->node->getAttribute($this->attributeName);
	if($this->attributeValue !== $currentAttributeValue) {
		$this->refreshAttribute();
	}

	$this->node->removeAttribute($this->attributeName);
	$attributeValue = implode($this->separator, $this->list);

	$this->node->setAttribute($this->attributeName, $attributeValue);
}

}#