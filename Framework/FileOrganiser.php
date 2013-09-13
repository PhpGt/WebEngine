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

private $_wwwDir;
private $_cacheFile;

public function __construct() {
	$this->_wwwDir = APPROOT . "/www";
	$this->_cacheFile = "{$this->_wwwDir}/www.cache";
}

/**
 * In production, cache is *always* valid if the www.cache file is present.
 * To invalidate, just remove the file.
 *
 * In non-production, creates an md5 hash of all the files within the
 * source directories and compares it to the contents of www.cache. If www.cache
 * does not exist, or the hash is different, the cache is invalid.
 *
 * @return bool True if the www directory needs refreshing.
 */
public function checkFiles() {
	$cacheFileExists = file_exists($this->_cacheFile);
	if(!$cacheFileExists) {
		return true;
	}
	if(App_Config::isProduction()) {
		return !$cacheFileExists;
	}

	$sourceDirectoryArray = array("Asset", "Script", "Style");
	$hashArray = array(
		md5(""),
	);

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

				$hashArray[] = md5_file($pathName);
			}
		}
	}

	$md5Str = "";
	foreach ($hashArray as $hash) {
		$md5Str .= $hash;
	}
	$md5 = md5($md5Str);

	$cacheHash = trim(file_get_contents($this->_cacheFile));

	// Returns if the two hashes are different.
	return $cacheHash !== $md5;
}

/**
 * Removes all files within any subdirectories in the www directory. Doesn't 
 * remove any files within the root www directory.
 */
public function clean() {
	$directoryArray = array("Asset", "Script", "Style");
	foreach($directoryArray as $directory) {
		$directoryPath = "{$this->_wwwDir}/$directory";

		if(!is_dir($directoryPath)) {
			continue;
		}

		foreach ($iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directoryPath,
				RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST) as $item) {

			$pathName = $iterator->getPathname();
			if($item->isDir()) {
				rmdir($pathName);
			}
			else {
				unlink($pathName);
			}
		}

		rmdir($directoryPath);
	}

	if(file_exists($this->_cacheFile)) {
		unlink($this->_cacheFile);
	}
}

/**
 * Performs the actual copying of resources within the three source directories.
 * Because PageTools can inject client-side resources in the DOM head, a list
 * of matching elements could be passed in to be included in the file copying.
 */
public function update($domHead = null) {
	$hashArray = array(
		md5(""),
	);

	$directoryArray = array("Asset", "Script", "Style");
	foreach($directoryArray as $directory) {
		$sourceDirectoryArray = array(
			GTROOT . "/$directory",
			APPROOT . "/$directory",
		);

		foreach ($sourceDirectoryArray as $sourceDirectory) {
			if(!is_dir($sourceDirectory)) {
				continue;
			}
			
			foreach ($iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($sourceDirectory,
					RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST) as $item) {

				$pathName = $iterator->getPathName();
				$subPathName = $iterator->getSubPathName();
				$fileName = $item->getFileName();
				if($fileName[0] === ".") {
					continue;
				}

				$destinationFile = "{$this->_wwwDir}/$directory/$subPathName";
				$destinationDir = dirname($destinationFile);

				if(!is_dir($destinationDir)) {
					mkdir($destinationDir, 0775, true);
				}

				if($item->isDir()) {
					continue;
				}

				copy($pathName, $destinationFile);
				$hashArray[] = md5_file($pathName);
			}
		}
	}

	$md5Str = "";
	foreach ($hashArray as $hash) {
		$md5Str .= $hash;
	}
	$md5 = md5($md5Str);

	file_put_contents($this->_cacheFile, $md5);
	return $md5;
}

/**
 * Script and Link elements in the HEAD can use server-side processing
 * techniques, such as using Sass/Scss and the //= require syntax within
 * JavaScript.
 */
public function processHead($domHead, $clientSideCompiler) {
	$count = 0;
	$styleElements = $domHead["link"];
	foreach ($styleElements as $el) {
		$pattern = "/\.scss$/i";
		$href = $el->getAttribute("href");
		
		if(!preg_match($pattern, $href)) {
			continue;
		}

		$pathArray = array(APPROOT . "/www/$href", GTROOT . "/www/$href");
		$path = null;
		foreach ($pathArray as $pathI) {
			if(is_null($path) && file_exists($pathI)) {
				$path = $pathI;
			}
		}

		$href = preg_replace($pattern, ".css", $href);
		$el->setAttribute("href", $href);

		if(is_null($path)) {
			continue;
		}


		if($clientSideCompiler !== false) {
			if($clientSideCompiler->process($path)) {
				$count++;
			}
		}
	}

	$scriptElements = $domHead["script"];
	foreach ($scriptElements as $el) {
		// TODO: 103: Implementation here.
	}

	return $count;
}

/**
 * If client-side compilation is turned on in the App_Config file, this function
 * compiles all client-side resources into a single resource and DELETES the
 * original files from the www directory. Note that if the original files do not
 * change, and the dom head stays the same, the check() function will not allow
 * this CPU-intensive function to be fired.
 */
public function compile($clientSideCompiler, $domHead, 
$combineForce = false, $compileForce = false) {
	$isCompiled = App_Config::isClientCompiled();
	if($isCompiled || $combineForce) {
		$clientSideCompiler->combine($domHead);
	}
	if($isCompiled || $compileForce) {
		$clientSideCompiler->compile();	
	}
	return;
}

/**
 * Removes any source files from the public web root.
 */
public function tidyProcessed() {
	$sourceExtensions = array("scss");

	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator("$this->_wwwDir",
			RecursiveDirectoryIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST) as $item) {

		$pathName = $iterator->getPathName();
		$fileName = $item->getFileName();
		$extension = strtolower($item->getExtension());
		
		if(!in_array($extension, $sourceExtensions)) {
			continue;
		}
		unlink($pathName);
	}
}

}#