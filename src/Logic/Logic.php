<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Logic;

abstract class Logic {

protected $api;
protected $content;
protected $session;
protected $data;
protected $params = [];

public $isDynamic = false;

public function __construct($api, $content, $session, $data) {
	$this->api = $api;
	$this->content = $content;
	$this->session = $session;
	$this->data = $data;
}

/**
 * Called to execute user code before page renders.
 *
 * @return void
 */
abstract public function go();

/**
 *
 */
public function setParams($params) {
	if(is_array($params)) {
		foreach ($params as $i => $value) {
			$this->params[$i] = $value;
		}
	}
	else {
		$this->params []= $params;
	}
	
	return $this->params;
}

}#