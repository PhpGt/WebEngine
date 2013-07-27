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
	$sourceDirectoryArray = array("Asset", "Script", "Style");

	$files = array();

	die("[[[" . APPROOT . "/{$sourceDirectoryArray[0]}]]]");

	foreach ($sourceDirectoryArray as $sourceDirectory) {
		foreach ($iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(APPROOT . "/$sourceDirectory",
				RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST) as $item) {

			var_dump($item);
			if(!$item->isDir()) {
				$files[] = $iterator->getSubPathName();
			}
		}
	}

	// TODO: Check PageTool files!
	var_dump($files);die();

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