<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;
class ApiDispatcher extends Dispatcher {

public function createResponseContent() {
	$apiObj = new \Gt\Response\StructuredData\Container();

	return $apiObj;
}

}#