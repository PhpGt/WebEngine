<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

class Response {

private $code;

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
 * @param int|ResponseCode $code HTTP response code
 *
 * @return ResponseCode The ResponseCode object holding the current response
 * code
 */
public function setCode($code) {
	if(is_int($code)) {
		$this->code->setCode($code);
	}
	else if($code instanceof ResponseCode) {
		$this->code = $code;
	}
	else {
		throw new \Gt\Core\Exception\InvalidArgumentTypeException();
	}

	return $this->code;
}

/**
 * The ResponseContent object represents the data that will be passed back to
 * the browser in the response.
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