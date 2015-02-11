<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

use \Gt\Core\ConfigObj;
use \Gt\Core\Path;

class Request {

const TYPE_PAGE			= "TYPE_PAGE";
const TYPE_API			= "TYPE_API";

const METHOD_GET		= "METHOD_GET";
const METHOD_POST		= "METHOD_POST";
const METHOD_PUT		= "METHOD_PUT";
const METHOD_DELETE		= "METHOD_DELETE";
const METHOD_HEAD		= "METHOD_HEAD";
const METHOD_OPTIONS	= "METHOD_OPTIONS";

public $uri;
public $ext;
public $method;
public $headers;
public $indexFilename;
public $forceExtension;

public $config;

/**
 * @param string $uri The requested absolute uri
 * @param ConfigObj $config Request configuration object
 */
public function __construct($uri, ConfigObj $config) {
	$this->uri = $uri;
	$this->ext = pathinfo($uri, PATHINFO_EXTENSION);
	$this->config = $config;

	$this->method = isset($_SERVER["REQUEST_METHOD"])
		? $_SERVER["REQUEST_METHOD"]
		: null;
	$this->headers = new HeaderList($_SERVER);
	$this->indexFilename = $config->index_filename;
	$this->forceExtension = $config->force_extension;
}

/**
 * Returns the type of request made, whether it is to a page or an API.
 *
 * @return string A Request type constant.
 */
public function getType() {
	$apiPrefix = substr(
		Path::get(Path::API, true),
		strlen(Path::get(Path::SRC, true))
	);
	$apiPrefix = strtolower($apiPrefix);

	if(strpos($this->uri, $apiPrefix) === 0) {
		return self::TYPE_API;
	}

	return self::TYPE_PAGE;
}

}#