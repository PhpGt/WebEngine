<?php final class FileOrganiser {
/**
 * This class works closely with ClientSideCompiler to ensure that all source
 * files are stored ouside of the webroot (www directory), but the compiled or
 * minified versions are copied correctly when required.
 *
 * The order of execution is controlled by the Dispatcher.
 *
 * 1) go functions are executed. This will trigger any PageTools' clientSide()
 * function, which adds <script> and <link> elements into the DOM head.
 * 2) FileOrganiser checks if files are needed to be written to www directory.
 * 3) FileOrganiser writes files to www directory.
 * 4) ClientSideCompiler pre-processes any SCSS source files.
 * 5) If client is compiled, FileOrganiser triggers the last step on the
 * ClientSideCompiler, minifying and compiling all files together and removing
 * the originals.
 *
 * All of this is only done if there are modifications to the source files since
 * the modified time within www directory.
 */

public function __construct() {}

/**
 * Uses the www/file.cache file to compare the cached state of the public web
 * files, in relation to the current files in the Asset, Script and Style
 * directories.
 *
 * @return array An array describing the www directory hierarchy to update to,
 * or null if there is nothing to be done.
 */
public function checkFiles() {
	$wwwDir = APPROOT . "/www";
	$cacheFile = "$wwwDir/file.cache";
	$sourceDirectoryArray = array("Asset", "Script", "Style");

	// Some directories should be ignored, as they are only copied if they are
	// used in the DOM head.
	$skipSubPaths = array(
		"All" => ["ReadMe.md"],
		"Asset" => [],
		"Style" => ["Font/*"],
		"Script" => [],
	);

	$cache = array();
	$cacheMTime = 0;
	if(file_exists($cacheFile)) {
		$cacheMTime = filemtime($cacheFile);
		$cacheString = file_get_contents($cacheFile);
		$cache = unserialize($cacheString);
	}
	else {
		$cacheString = serialize($cache);
		file_put_contents($cacheFile, $cacheString);
	}

	$files = array();
	$fileMTimeLatest = 0;

	// First build up the array of files in the source directories.
	foreach ($sourceDirectoryArray as $sourceDirectory) {
		// GTROOT comes first in the array, so that they will be overrided by
		// any files that have the same name in the APPROOT.
		$directoryPathArray = array(
			GTROOT . "/$sourceDirectory",
			APPROOT . "/$sourceDirectory",
		);

		foreach($directoryPathArray as $directoryPath) {
			if(!is_dir($directoryPath)) {
				continue;
			}

			foreach ($iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($directoryPath,
					RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST) as $item) {

				$pathName = $iterator->getPathName();
				$subPathName = $iterator->getSubPathName();

				// Don't check on hidden files or directories.
				if(strpos($item->getFileName(), ".") === 0
				|| $item->isDir()) {
					continue;
				}

				// Ignore directories that are to be checked in the DOM head.
				$ignoreCheck = array_merge(
					$skipSubPaths[$sourceDirectory],
					$skipSubPaths["All"]
				);
				foreach ($ignoreCheck as $ignore) {
					if(fnmatch($ignore, $subPathName)) {
						continue 2;
					}	
				}

				if(!isset($files[$sourceDirectory])) {
					$files[$sourceDirectory] = array();
				}

				$files[$sourceDirectory][] = [
					"SubPathName" => $subPathName,
					"FileMTime" => filemtime($pathName),
				];
			}
		}
	}

	if($fileMTimeLatest < $cacheMTime) {
		return null;
	}

	// Loop over www directory files, removing them from the $files array if
	// they already exist.
	file_put_contents($cacheFile, serialize($files));

	return $files;
}

/**
 * Removes any old files from www, but doesn't remove files that have been put
 * there manually (for example, adding robots.txt to www should not get removed)
 */
public function clean($fileList) {
	return;
}

/**
 * Performs the actual copying of changed resources.
 */
public function update($fileList) {
	return;
}

/**
 * Some files will require preprocessing, such as SCSS source files. The source
 * files will be present in the www directory structure, so this function will
 * REPLACE the source files.
 */
public function process($clientSideCompiler) {
	return;
}

/**
 * If client-side compilation is turned on in the App_Config file, this function
 * compiles all client-side resources into a single resource and DELETES the
 * original files from the www directory. Note that if the original files do not
 * change, and the dom head stays the same, the check() function will not allow
 * this CPU-intensive function to be fired.
 */
public function compile($clientSideCompiler) {
	return;
}

}#