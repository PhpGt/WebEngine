<?php
namespace Gt\WebEngine\Test\FileSystem;

use Gt\WebEngine\Test\Helper\Helper;
use Gt\WebEngine\FileSystem\DirectoryWalker;
use PHPUnit\Framework\TestCase;

class DirectoryWalkerTest extends TestCase {
	/** @dataProvider dataProviderParentExists */
	public function testFindParentThatExists(string $directory) {
// Get a $parent_path further up the tree than the provided $directory:
		$directoryList = explode(DIRECTORY_SEPARATOR, $directory);
		$numDirectories = count($directoryList);
		foreach($directoryList as $webenginePosition => $value) {
			if($value === "webengine") {
				break;
			}
		}
		$randomDirectoryCount = mt_rand($webenginePosition, $numDirectories);
		$directoryListUpToParent = array_splice($directoryList, 0, $randomDirectoryCount);
		$parentDirectory = implode(DIRECTORY_SEPARATOR, $directoryListUpToParent);

// Create a directory in the $parent_path to look for:
		$directoryName = $this->getRandomName();
		mkdir(implode(DIRECTORY_SEPARATOR, [
				$parentDirectory,
				$directoryName,
			]),
			0775,
			true
		);
		$directoryWalker = new DirectoryWalker($directory);

		self::assertEquals(
			$parentDirectory,
			$directoryWalker->findParentContaining($directoryName)
		);
	}

	public function dataProviderParentExists():array {
		return $this->dataProviderParent(true);
	}

	public function dataProviderParentNotExists():array {
		return $this->dataProviderParent(false);
	}

	protected function dataProviderParent(bool $exists, int $num = 10):array {
		$tmp = Helper::getTmpDir();
		$data = [];

		for($i = 0; $i < $num; $i++) {
			$directory = $tmp;
			$numberOfChildren = rand(5, 25);
			for($childNum = 0; $childNum < $numberOfChildren; $childNum++) {
				$directory .= DIRECTORY_SEPARATOR . $this->getRandomName();
			}

			$data []= [$directory];
		}

		if($exists) {
			foreach($data as $parameters) {
				if(!is_dir($parameters[0])) {
					mkdir($parameters[0], 0775, true);
				}
			}
		}

		return $data;
	}

	protected function getRandomName(int $maxLength = 10):string {
		$lengthOfDirectoryName = rand(2, $maxLength);
		return bin2hex(
			random_bytes(
				floor($lengthOfDirectoryName / 2)
			)
		);
	}
}
