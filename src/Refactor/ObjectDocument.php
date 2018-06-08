<?php
namespace Gt\WebEngine\Refactor;

use Gt\Dom\Document;
use Gt\Dom\LiveProperty;
use Gt\Dom\ParentNode;
use stdClass;

class ObjectDocument extends Document {
	use LiveProperty, ParentNode;

	protected $type;

	public function __construct(string $document = "", string $type) {
		parent::__construct();

		$this->type = $type;

		if($this->isJsonString($document)) {
			$this->loadJSON($document);
		}
		elseif($this->isXmlString($document)) {
			$this->loadXML($document);
		}
		else {
			throw new DocumentStringParseException("Unknown document type");
		}
	}

	public function isJsonString(string $document):bool {
		return $this->isFirstNonWhiteSpaceCharacter($document, "{");
	}

	public function isXmlString(string $document):bool {
		return $this->isFirstNonWhiteSpaceCharacter($document, "<");
	}

	protected function isFirstNonWhiteSpaceCharacter(
		string $document,
		string $firstChar
	):bool {
		$i = 0;

		do {
			$char = $document[$i];
		}while(trim($char) === "");

		return $char === $firstChar;
	}

	/**
	 * TODO: This function is not yet implemented. It acts as a placeholder until it is
	 * extracted and implemented in its own repository, ObjectDocument.
	 */
	public function loadJSON(string $jsonString):void {
		$json = json_decode($jsonString);

		foreach($json as $key => $value) {
			$node = $this->createElement($key, $value);
			$this->appendChild($node);
		}
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
}