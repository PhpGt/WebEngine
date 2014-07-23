<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
class ResponseCodeFactory {

/**
 * @param string $uri The requested URI, used for locating Code classes
 * @param string $type A Request::TYPE_* constant.
 * @param ApiFactory $apiFactory The API factory for constructing the Code
 * class(es) with.
 * @param DatabaseFactory $dbFactory The Database factory for constructing
 * the Code class(es) with.
 */
public function create($uri, $type, 
ApiFactory $apiFactory, DatabaseFactory $dbFactory, ResponseContent $content) {
	// TODO.
}

}#