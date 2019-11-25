<?php
namespace Gt\WebEngine\Logic;

use Iterator;

class LogicPropertyStoreReader extends LogicPropertyStore implements Iterator {
	/** @var LogicPropertyStore */
	protected $propertyStore;
	protected $iteratorKey;
	protected $iteratorStrings;

	public function __construct(
		LogicPropertyStore $logicPropertyStore
	) {
		$this->propertyStore = $logicPropertyStore;
	}

	/** @link https://php.net/manual/en/iterator.rewind.php */
	public function rewind():void {
		$this->iteratorKey = 0;
		$this->iteratorStrings = array_keys($this->propertyStore->kvp);
	}

	/** @link https://php.net/manual/en/iterator.current.php */
	public function current() {
		return $this->propertyStore->kvp[$this->key()];
	}

	/** @link https://php.net/manual/en/iterator.next.php */
	public function next():void {
		$this->iteratorKey++;
	}

	/** @link https://php.net/manual/en/iterator.key.php */
	public function key():?string {
		return $this->iteratorStrings[$this->iteratorKey] ?? null;
	}

	/** @link https://php.net/manual/en/iterator.valid.php */
	public function valid():bool {
		return isset($this->propertyStore->kvp[$this->key()]);
	}
}