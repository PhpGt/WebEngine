<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;

class Request {

const TYPE_PAGE			= "TYPE_PAGE";
const TYPE_API			= "TYPE_API";

const METHOD_GET		= "METHOD_GET";
const METHOD_POST		= "METHOD_POST";
const METHOD_PUT		= "METHOD_PUT";
const METHOD_DELETE		= "METHOD_DELETE";
const METHOD_HEAD		= "METHOD_HEAD";
const METHOD_OPTIONS	= "METHOD_OPTIONS";

public $method;
public $uri;

private $config;

/**
 * @param string $uri The requested absolute uri
 * @param Obj $config Request configuration object
 */
public function __construct($uri, $config) {
	$this->uri = $uri;
	$this->ext = pathinfo($uri, PATHINFO_EXTENSION);
	$this->config = $config;
}

/**
 * Returns the type of request made, whether it is to a page or an API.
 * @return mixed A Request type constant.
 */
public function getType() {
	$apiPrefix = "/" . $this->config->api_prefix;

	if(strpos($this->uri, $apiPrefix) === 0) {
		return Request::TYPE_API;
	}

	return Request::TYPE_PAGE;
}

}#