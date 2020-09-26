<?php
namespace Gt\WebEngine\Test;

use Gt\WebEngine\Test\Helper\FunctionOverride\Override;
use PHPUnit\Framework\TestCase;

class WebEngineTestCase extends TestCase {
	private $createdTmpDirs = [];

	public function tearDown():void {
		foreach($this->createdTmpDirs as $dir) {
			$this->recursiveUnlink($dir);
		}

		parent::tearDown();
	}

	public function getTmpDir(string $prefix = null):string {
		if(is_null($prefix)) {
			$prefix = "webengine-test-case";
		}

		$dir = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"webengine",
			uniqid($prefix . "-"),
		]);
		if(!is_dir($dir)) {
			mkdir($dir, 0775, true);
		}

		$this->createdTmpDirs []= $dir;

		return $dir;
	}

	public static function expectCallsToFunction(
		string $functionName,
		array $parameterCalls
	) {
		self::assertEquals(
			count($parameterCalls),
			Override::getNumCalls($functionName)
		);

		$actualParameterCalls = Override::getCalls($functionName);
		foreach($parameterCalls as $i => $expectedParam) {
			self::assertEquals(
				$expectedParam,
				$actualParameterCalls[$i]
			);
		}
	}

	private function recursiveUnlink(string $path):void {
		$contents = glob("$path/*");
		foreach($contents as $filePath) {
			if(is_file($filePath)) {
				unlink($filePath);
			}
			elseif(is_dir($filePath)) {
				$this->recursiveUnlink($filePath);
			}
		}

		rmdir($path);
	}
}