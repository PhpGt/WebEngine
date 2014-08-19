<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

abstract class PageLogic extends Logic {

protected $dom;

public function __construct($apiFactory, $dbFactory, $content) {
	parent::__construct($apiFactory, $dbFactory, $content);
	$this->dom = $content;
}

}#