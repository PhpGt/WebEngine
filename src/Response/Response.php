<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;
use Gt\Request\Request;

class Response {

public $content;

private $code;
private $config;

public function __construct($config) {
	$this->config = $config;
}

public function setCode(ResponseCode $code) {
	$this->code = $code;
}

}#