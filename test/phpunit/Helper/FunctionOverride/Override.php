<?php
namespace Gt\WebEngine\Test\Helper\FunctionOverride;

class Override {
	private static $calls = [];

	public static function replace(
		string $functionName,
		string $testDir
	) {
		/** @noinspection PhpIncludeInspection */
		require_once($testDir . "/" . "func_$functionName.php");
	}

	public static function recordCall(
		string $functionName,
		array $params
	) {
		if(strstr($functionName, "\\")) {
			$functionName = substr(
				$functionName,
				strrpos($functionName, "\\") + 1
			);
		}

		if(!isset(self::$calls[$functionName])) {
			self::$calls[$functionName] = [];
		}

		self::$calls[$functionName] []= $params;
	}

	public static function getNumCalls(string $functionName):int {
		$paramCallsForFunction = self::$calls[$functionName] ?? [];
		return count($paramCallsForFunction);
	}

	public static function getCalls(string $functionName):array {
		return self::$calls[$functionName] ?? [];
	}
}