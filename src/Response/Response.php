<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

class Response {

public $content;
public $config;
public $production;
public $code;

/**
 *
 */
public function __construct($config, $production = false) {
	$this->config = $config;
	$this->production = $production;
	$this->code = new ResponseCode();
}

/**
 * Sets the response's content object that must be an object that extends
 * ResponseContent.
 *
 * @param \Gt\Dom\Document|\Gt\Api\Payload $paramname description
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