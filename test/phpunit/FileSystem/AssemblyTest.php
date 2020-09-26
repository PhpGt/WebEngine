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

	public function testToStringMissingFile() {
		$path = "/example";
		$exampleString = "This is an example";
		$this->generateAssemblyFiles([
			$path => $exampleString
		]);

		$sut = new Assembly(
			$this->tmpDir,
			"/",
			"does-not-match",
			["test"]
		);
		self::assertSame("", (string)$sut);
	}

	public function testToStringBeforeAfter() {
		$path = "/example";
		$exampleBeforeStirng = "This is BEFORE";
		$exampleString = "This is an example";
		$exampleAfterString = "This is AFTER";
		$this->generateAssemblyFiles([
			$path => $exampleString,
			"/_before" => $exampleBeforeStirng,
			"/_after" => $exampleAfterString,
		]);

		$sut = new Assembly(
			$this->tmpDir,
			"/",
			"example",
			["test"],
			["_header", "_before"],
			["_footer", "_after"],
		);
		self::assertEquals(
			$exampleBeforeStirng . $exampleString . $exampleAfterString,
			$sut
		);
	}

	public function testToStringNestedBefore() {
		$path = "/nested/example/path";
		$exampleBeforeStringOuter = "This is BEFORE - outer";
		$exampleBeforeString = "This is BEFORE";
		$exampleString = "This is an example";
		$this->generateAssemblyFiles([
			$path => $exampleString,
			"/_before" => $exampleBeforeStringOuter,
			"/nested/_before" => $exampleBeforeString,
		]);

		$sut = new Assembly(
			$this->tmpDir,
			"/nested/example",
			"path",
			["test"],
			["_before"]
		);
		self::assertEquals(
			$exampleBeforeStringOuter . $exampleBeforeString . $exampleString,
			$sut
		);
	}

	public function testToStringNestedAfter() {
		$path = "/nested/example/path";
		$exampleAfterStringOuter = "This is AFTER - outer";
		$exampleAfterString = "This is AFTER";
		$exampleString = "This is an example";
		$this->generateAssemblyFiles([
			$path => $exampleString,
			"/_after" => $exampleAfterStringOuter,
			"/nested/_after" => $exampleAfterString,
		]);

		$sut = new Assembly(
			$this->tmpDir,
			"/nested/example",
			"path",
			["test"],
			[],
			["_after"]
		);
		self::assertEquals(
			$exampleString . $exampleAfterString . $exampleAfterStringOuter,
			(string)$sut
		);
	}

	private function generateAssemblyFiles(array $pathContents):void {
		foreach($pathContents as $path => $content) {
			$absoluteFilePath = $this->tmpDir . "$path.test";

			if(!is_dir(dirname($absoluteFilePath))) {
				mkdir(dirname($absoluteFilePath), 0775, true);
			}

			file_put_contents($absoluteFilePath, $content);
		}
	}
}