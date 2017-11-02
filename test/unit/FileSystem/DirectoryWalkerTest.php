<?php
namespace Gt\Test\FileSystem;

use Gt\Test\Helper;
use Gt\FileSystem\DirectoryWalker;
use PHPUnit\Framework\TestCase;

class DirectoryWalkerTest extends TestCase {
	/**
	 * @dataProvider dataProviderParentExists
	 */
	public function testFindParentThatExists(string $directory) {
// Get a $parent_path further up the tree than the provided $directory:
		$parentDirectories = explode("/", $directory);
		$parent_depth = rand(2, count($parentDirectories) - 1);
		array_splice($parentDirectories, $parent_depth);
		$parentPath = implode("/", $parentDirectories);

// Create a directory in the $parent_path to look for:
		$directoryName = $this->getRandomName();
		mkdir("$parentPath/$directoryName", 0775, true);
		$directoryWalker = new DirectoryWalker($directory);

		self::assertEquals(
			$parentPath,
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
		$tmp = Helper::getTempDirectory();
		$data = [];

		for($i = 0; $i < $num; $i++) {
			$directory = $tmp;
			$numberOfChildren = rand(5, 25);
			for($childNum = 0; $childNum < $numberOfChildren; $childNum++) {
				$directory .= "/" . $this->getRandomName();
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
