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

public $code;
public $content;

private $config;

public function __construct($config) {
	$this->config = $config;
}

public function setCode(ResponseCode $code) {
	$this->code = $code;
}

public function setContentObject(ResponseContent $content) {
	$this->content = $content;
}

}#