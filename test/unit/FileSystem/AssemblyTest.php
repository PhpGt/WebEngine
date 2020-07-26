<?php
namespace Gt\WebEngine\Test\FileSystem;

use Gt\WebEngine\FileSystem\Assembly;
use PHPUnit\Framework\TestCase;

class AssemblyTest extends TestCase {
	private string $tmpDir;

	public function setUp():void {
		$this->tmpDir = implode(DIRECTORY_SEPARATOR, [
			sys_get_temp_dir(),
			"phpgt",
			"webengine",
			"test",
			"assembly",
			uniqid()
		]);
		mkdir($this->tmpDir, 0775, true);
	}

	public function tearDown():void {
		exec("rm -rf " . $this->tmpDir);
	}

	public function testToString() {
		$path = "/example";
		$exampleString = "This is an example";
		$this->generateAssemblyFiles([
			$path => $exampleString
		]);

		$sut = new Assembly(
			$this->tmpDir,
			"/",
			"example",
			["test"]
		);
		self::assertEquals($exampleString, $sut);
	}

	private function generateAssemblyFiles(array $pathContents):void {
		foreach($pathContents as $path => $content) {
			$absoluteFilePath = implode(DIRECTORY_SEPARATOR, [
				$this->tmpDir,
				"$path.test",
			]);

			if(!is_dir(dirname($absoluteFilePath))) {
				mkdir(dirname($absoluteFilePath), 0775, true);
			}

			file_put_contents($absoluteFilePath, $content);
		}
	}
}