<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Dispatcher;
use \Gt\Request\Request;
use \Gt\Response\Response;
use \Gt\Api\ApiFactory;
use \Gt\Database\DatabaseFactory;

abstract class Dispatcher {

private $request;
private $response;
private $apiFactory;
private $dbFactory;

public function __construct(Request $request, Response $response, 
ApiFactory $apiFactory, DatabaseFactory $dbFactory) {
	$this->request = $request;
	$this->response = $response;
	$this->apiFactory = $apiFactory;
	$this->dbFactory = $dbFactory;
}

}#