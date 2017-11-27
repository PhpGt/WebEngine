<?php
namespace Gt\WebEngine\Privacy;

use ArrayAccess;

class GlobalStub implements ArrayAccess {
	const ERROR_MESSAGE = "Global variables are disabled - see https://php.gt/globals";

	public function offsetExists($offset) {
		$this->throwException();
	}

	public function offsetGet($offset) {
		$this->throwException();
	}

	public function offsetSet($offset, $value) {
		$this->throwException();
	}

	public function offsetUnset($offset) {
		$this->throwException();
	}

	protected function throwException() {
		throw new GlobalAccessException(self::ERROR_MESSAGE);
	}

	public function __debugInfo():array {
		return ["ERROR" => (string)$this];
	}

	public function __toString() {
		return self::ERROR_MESSAGE;
	}
}