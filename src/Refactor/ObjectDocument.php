<?php
namespace Gt\WebEngine\Refactor;

use Gt\Dom\Document;
use Gt\Dom\LiveProperty;
use Gt\Dom\ParentNode;
use stdClass;

class ObjectDocument extends Document {
	use LiveProperty, ParentNode;

	protected $type;

	public function __construct(string $document, string $type) {
		parent::__construct();

		$this->type = $type;
	}

	public function __toString() {
		return $this->render();
	}

	protected function render():string {
		if($this->type === "application/json") {
			return $this->saveJSON();
		}

		return $this->saveHTML();
	}

	protected function saveJSON():string {
		$json = new StdClass();

		foreach($this->children as $child) {
			$key = $child->tagName;
			$json->$key = $child->nodeValue;
		}

		return json_encode($json);
	}

	protected function getStringValue($type, $value):string {
		switch($type) {
		case "bool":
		case "boolean":
			return $value ? "true" : "false";

		case "object":
			// TODO: Recursively load.
			return "(object)";

		case "int":
		case "integer":
		case "float":
		case "double":
		default:
			return (string)$value;
		}
	}
}