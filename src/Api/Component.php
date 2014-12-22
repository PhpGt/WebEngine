<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Api;

use \Gt\Core\Path;

class Component {

private $name;
private $parent;
private $version;

public function __construct($name, $version, $parent = null) {
	$this->name = $name;
	$this->parent = $parent;
	$this->version = $version;
}

public function __get($name) {
	return new Component($name, $this->version, $this);
}

public function __call($name, $args) {
	$path = $this->getPath();
	$subPath = $this->getSubPath($name);

	return new Endpoint($path, $subPath, $args);
}

private function getPath() {
	$path = Path::get(Path::API);
	$path .= "/$this->version";
	return $path;
}

private function getSubPath($end = "") {
	$path = "";

	$reference = $this;
	do {
		$path = $reference->getName() . "/$path";

		$reference = $reference->getParent();
	} while(!is_null($reference));

	return $path . $end;
}

public function getParent() {
	return $this->parent;
}

public function getName() {
	return $this->name;
}

}#