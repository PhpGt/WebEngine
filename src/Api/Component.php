<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Api;

class Component {

private $name;
private $config;
private $parent;

public function __construct($name, $config, $parent = null) {
	$this->name = $name;
	$this->config = $config;
	$this->parent = $parent;
}

public function __get($name) {
	return new Component($name, $this->config, $this);
}

public function __call($name, $args) {
	$path = $this->getPath();
	var_dump($name, $args);die();
}

private function getPath() {
	$path = "";

	$reference = $this;
	do {
		$path = $reference->getName() . "/$path";

		$reference = $reference->getParent();
	} while(!is_null($reference->parent)) {

	var_dump($path);die("!!!!!!!!!!!!!");
}

public function getParent() {
	return $this->parent;
}

public function getName() {
	return $this->name;
}

}#