<?php class Css2Xpath {
/**
 * Css2Xpath class utilising Zend Framework 2's utility. Located in
 * /library/Zend/Dom/Css2Xpath.php
 * Taken from commit b8b7bb on 21st Jan 2013.
 */
private $_selector;			// Original CSS selector.
private $_xpath;			// XPath version of CSS selector.

public function __construct($selector) {
	require_once(__DIR__ . "/Css2Xpath/Css2Xpath.php");
	$this->_xpath = Zend\Dom\Css2Xpath::transform($selector);
	return $this->_xpath;
}

public function __toString() {
	return $this->_xpath;
}

}#