<?php
namespace Gt\WebEngine\Logic;

class DynamicPath {
	private $keyValuePairs;

	public function __construct(iterable $keyValuePairs) {
		$this->keyValuePairs = $keyValuePairs;
	}

	public function get(string $key):?string {
		return $this->keyValuePairs[$key] ?? null;
	}
}