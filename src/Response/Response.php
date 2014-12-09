<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

class Response {

public $code;
public $content;

public $config;
public $production;

/**
 *
 */
public function __construct($config, $production = false) {
	$this->config = $config;
	$this->production = $production;
}

/**
 *
 */
public function setCode(ResponseCode $code) {
	$this->code = $code;
}

/**
 *
 */
public function setContentObject(ResponseContent $content) {
	$this->content = $content;
	$this->content->config = $this->config;
}

/**
 * @param string $name Configuration option name
 *
 * @return string Configuration option value
 */
public function getConfigOption($name) {
	return $this->config->$name;
}

}#