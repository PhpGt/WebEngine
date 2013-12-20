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

}#