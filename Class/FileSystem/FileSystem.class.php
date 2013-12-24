<?php class FileSystem {

/**
 * Recursively iterate over a directory and its contents.
 * @param $dirName string The base directory to iterate over.
 * @param $output mixed The output variable to build up within the callback.
 * @param $context mixed A context variable to pass to the function. This can be
 * an array of variables, or a single variable.
 * @param $callback Callback The function to call for each child item. Callback
 * takes three parameters: 
 * 1) The SplFileInfo object.
 * 2) The iterator object.
 * 3) The output variable.
 * @param $recursiveDirectoryIteratorFlags int Optional.
 * @param $recursiveIteratorIteratorFlags int Optional.
 * @return array An array contining the output of each callback.
 *
 * See http://www.php.net/manual/en/class.recursivedirectoryiterator.php
 * and http://www.php.net/manual/en/class.recursiveiteratoriterator.php
 */
public static function loopDir($dirName, &$output, $callback, $context = null, 
$rdFlags = RecursiveDirectoryIterator::SKIP_DOTS,
$riFlags = RecursiveIteratorIterator::SELF_FIRST) {

	$returnArray = array();

	foreach ($iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dirName, $rdFlags), $riFlags) as $item) {
		$returnArray[] = $callback($item, $iterator, $output, $context);
	}

	return $returnArray;
}

/**
 * Removes the provided file. If the file is a directory, remove it along with
 * all of its contents.
 *
 * @param $file string The absolute path to remove.
 * @return int|bool False on failure, otherwise will return the count of files
 * removed.
 */
public static function remove($file) {
	if(!file_exists($file)) {
		return 0;
	}

	if(is_dir($file)) {
		$count = 0;

		self::loopDir($file, $count, 
		function($item, $iterator, &$count) {
			$pathname = $item->getPathname();
			if($item->isDir()) {
				if(rmdir($pathname)) {
					$count++;
				}
			}
			else {
				if(unlink($pathname)) {
					$count++;
				}				
			}
		}, null,
			RecursiveDirectoryIterator::SKIP_DOTS,
			RecursiveIteratorIterator::CHILD_FIRST
		);

		if(rmdir($file)) {
			$count++;
		}

		return $count;
	}
	else {
		if(unlink($file)) {
			return 1;
		}
		else {
			return false;
		}
	}
}

/**
 * Recursive copy.
 */
public static function copy($source, $destination) {
	$count = 0;

	self::loopDir($source, $count, 
	function($item, $iterator, &$count, $destination) {
		if($item->isDir()) {
			return;
		}
		
		if(substr($destination, -1) == "/") {
			$destination = substr($destination, 0, -1);
		}

		$pathname = $item->getPathname();
		$subpath = $iterator->getSubPathname();
		$destinationPath = $destination . "/" . $subpath;

		if(!is_dir(dirname($destinationPath))) {
			mkdir(dirname($destinationPath), 0775, true);
		}

		if(copy($pathname, $destinationPath)) {
			$count++;
		}

	}, $destination);

	return $count;
}

}#