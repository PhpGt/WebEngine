<?php
namespace Gt\WebEngine\Debug;

class Timer {
	private float $startTime;
	private float $endTime;

	public function __construct() {
		$this->startTime = microtime(true);
	}

	public function stop():void {
		$this->endTime = microtime(true);
	}

	public function getDelta():float {
		return $this->endTime - $this->startTime;
	}
}
