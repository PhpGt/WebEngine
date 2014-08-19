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
protected $db;
protected $content;

public function __construct($apiFactory, $dbFactory, $content) {
	$this->api = $apiFactory;
	$this->db = $dbFactory;
	$this->content = $content;
}

abstract public function go();

}#