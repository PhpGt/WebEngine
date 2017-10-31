<?php
namespace Gt\Test\FileSystem;

use Gt\Test\Helper;
use Gt\FileSystem\DirectoryWalker;
use PHPUnit\Framework\TestCase;

class DirectoryWalkerTest extends TestCase {
	/**
	 * @dataProvider parentExistsProvider
	 */
	public function testFindParentThatExists(string $directory) {
// Get a $parent_path further up the tree than the provided $directory:
		$parent_directories = explode("/", $directory);
		$parent_depth = rand(2, count($parent_directories) - 1);
		array_splice($parent_directories, $parent_depth);
		$parent_path = implode("/", $parent_directories);

// Create a directory in the $parent_path to look for:
		$directory_name = $this->getRandomName();
		mkdir("$parent_path/$directory_name", 0775, true);
		$directory_walker = new DirectoryWalker($directory);

		self::assertEquals(
			$parent_path,
			$directory_walker->findParentContaining($directory_name)
		);
	}

	public function parentExistsProvider():array {
		return $this->parentProvider(true);
	}

	public function parentNotExistsProvider():array {
		return $this->parentProvider(false);
	}

	protected function parentProvider(bool $exists, int $num = 10):array {
		$tmp = Helper::getTempDirectory();
		$data = [];

		for($i = 0; $i < $num; $i++) {
			$directory = $tmp;
			$number_of_children = rand(5, 25);
			for($child_num = 0; $child_num < $number_of_children; $child_num++) {
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

	protected function getRandomName(int $max_length = 10):string {
		$length_of_directory_name = rand(2, $max_length);
		return bin2hex(
			random_bytes(
				floor($length_of_directory_name / 2)
			)
		);
	}
}
