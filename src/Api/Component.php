<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Api;

use \Gt\Core\Path;

class Component {

private $name;
private $parent;
private $api;
private $version;

public function __construct($name, $api, $parent = null) {
	$this->name = $name;

	$this->api = $api;
	$this->parent = $parent;
	$this->version = $this->api->getVersion();
}

public function __get($name) {
	return new Component($name, $this->api, $this);
}

public function __call($name, $args) {
	$path = $this->getPath();
	$subPath = $this->getSubPath($name);

	$params = [];
	if(!empty($args)) {
		$params = $args[0];
	}

	$endpoint = new Endpoint($path, $subPath, $params, $this->api);
	return $endpoint->execute();
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