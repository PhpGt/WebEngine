<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Logic;

abstract class Logic {

protected $api;
protected $content;
protected $session;
protected $params = [];

public $isDynamic = false;

public function __construct($api, $content, $session) {
	$this->api = $api;
	$this->content = $content;
	$this->session = $session;
}

/**
 * Called to execute user code before page renders.
 *
 * @return void
 */
abstract public function go();

/**
 *
 */
public function setParams($params) {
	foreach ($params as $i => $value) {
		$this->params[$i] = $value;
	}
	return $this->params;
}

}#