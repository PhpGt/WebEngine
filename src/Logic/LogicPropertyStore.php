<?php
namespace Gt\WebEngine\Logic;

/**
 * Common Logic objects, such as the Page Logic that is called for every request
 * under a particular URI directory, can set properties on the other Logic
 * objects that are executed.
 *
 * For example, in _CommonPage, call $this->logicProperties->set("someKey", 123);
 * and then within another class such as IndexPage, declare a public property
 * of name $someKey, and it will automatically be set to the correct value.
 */
class LogicPropertyStore {
	const FORBIDDEN_LOGIC_PROPERTIES = [
		"viewModel",
		"config",
		"server",
		"input",
		"cookie",
		"session",
		"database",
		"dynamicPath",
	];

	protected $kvp = [];

	public function set(string $key, $value) {
		$this->kvp[$key] = $value;
	}
}