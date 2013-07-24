<?php final class FileOrganiser {
/**
 * All files are stored outside of the webroot (www directory), so it is the job
 * of the FileOrganiser to copy the required files into the webroot when
 * required. The files may need to be minified and compiled before they are
 * copied.
 */

// List of wildcards to skip.
private $_skipWc = array(
	"*.scss", 
	"ReadMe.md", 
);

public function __construct($config) {
	// Don't do anything if there are no changes in the source directories.
	if(!$this->changedFiles()) {
		return;
	}

	$this->removePublicFiles();
	$this->copyFilesToPublic($config);
}

/**
 * Checks to see if any source files have changed since the last public copy.
 * @param string $dir Optional. The directory to check. Defaults to /www.
 * @return bool True if there are changes, false if there are no changes.
 */
private function changedFiles($dir = "/www") {
	$dir = APPROOT . $dir;
	$sourceChanged = false;

	$wwwMTime = $this->recursiveMTime("/www");
	$searchDirArray = array(
		"Script",
		"Style",
	);
	foreach ($searchDirArray as $searchDir) {
		$searchDir = APPROOT . "/$searchDir";
		$dirMTime = $this->recursiveMTime($searchDir);
		if($dirMTime > $wwwMTime) {
			$sourceChanged = true;
		}
	}

	return $sourceChanged;
}

/**
 * Gets the latest modified file's timestamp in a directory structure, 
 * recursively.
 * @param  string $dir The absolute directory path to search.
 * @return int         Timestamp of the latest modified file, or 0 if no files.
 */
private function recursiveMTime($dir) {
	$timestamp = 0;
	if(!is_dir($dir)) {
		return 0;
	}

	$iterator = new RecursiveDirectoryIterator($dir,
		RecursiveIteratorIterator::CHILD_FIRST
		| FilesystemIterator::SKIP_DOTS
		| FilesystemIterator::UNIX_PATHS);
	
	$fileList = new RecursiveIteratorIterator(
		$iterator, RecursiveIteratorIterator::SELF_FIRST);
	foreach ($fileList as $key => $value) {
		if(!is_file($key)) {
			continue;
		}
		if($value->getMTime() > $timestamp) {
			$timestamp = $value->getMTime();
		}
	}
	
	return $timestamp;
}

/**
 * Deletes all files within the www directory, apart from the vital Go.php.
 * This function is recursive, so will remove all Assets and files within Style.
 */
private function removePublicFiles() {
	$dir = APPROOT . "/www/";
	if(!is_dir($dir)) {
		// TODO: Throw proper error here.
		die("ERROR: Public web root is not a directory.");
	}

	$iterator = new RecursiveDirectoryIterator($dir,
		RecursiveIteratorIterator::CHILD_FIRST
		| FilesystemIterator::SKIP_DOTS
		| FilesystemIterator::UNIX_PATHS);
	$fileList = new RecursiveIteratorIterator($iterator, 
		RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($fileList as $key => $value) {
		if(strstr($value->getFilename(), ".php")) {
			continue;
		}
		if(is_dir($key)) {
			rmdir($key);
			continue;
		}
		unlink($key);
	}
}

private function copyFilesToPublic($config) {
	// The order of copying is vital here; some applications can overwrite the
	// files supplied by GT with their own, in whicch case the application's
	// version of the file will be preferred.

	$wwwDir = APPROOT . "/www";
	$copyDirArray = array(
		GTROOT  . "/Style/Img/"		=> $wwwDir . "/Style/Img/",
		GTROOT  . "/Style/Font/"	=> $wwwDir . "/Font/",
		APPROOT . "/Style/Img"		=> $wwwDir . "/Style/Img/",
		APPROOT . "/Style/Font/"	=> $wwwDir . "/Font/",
		APPROOT . "/Asset/"			=> $wwwDir . "/Asset/",
	);
	
	foreach ($copyDirArray as $source => $dest) {
		$this->copyFiles($source, $dest);
	}

	if($config::isClientCompiled()) {
		if(file_exists(APPROOT . "/Script/Script.min.js")) {
			copy(APPROOT . "/Script/Script.min.js", 
				APPROOT . "/www/Script.min.js");
		}
		if(file_exists(APPROOT . "/Style/Style.min.css")) {
			copy(APPROOT . "/Style/Style.min.css", 
				APPROOT . "/www/Style.min.css");	
		}
	}
	else {
		$this->copyFiles(GTROOT  . "/Script/", APPROOT . "/www");
		$this->copyFiles(GTROOT  . "/Style/",  APPROOT . "/www");
		$this->copyFiles(APPROOT . "/Script/", APPROOT . "/www");
		$this->copyFiles(APPROOT . "/Style/",  APPROOT . "/www");
	}

	return;
}

/**
 * Copies the contents of source directory to destination, skipping optional
 * file extensions.
 * @param  string  $source    Source directory to copy from.
 * @param  string  $dest      Destination directory to copy to.
 * @param  boolean $recursive Optional. Whether to perform a deep copy. Defaults
 * to true.
 */
private function copyFiles($source, $dest, $recursive = true) {
	if(!is_dir($source)) {
		return;
	}

	$dh = opendir($source);
	if(!is_dir($dest)) {
		mkdir($dest, 0775, true);
	}

	while(false !== ($name = readdir($dh)) ) {
		if($name[0] == ".") {
			continue;
		}

		$skip = false;
		foreach ($this->_skipWc as $skipWc) {
			if(fnmatch($skipWc, $name)) {
				$skip = true;
				break;
			}
		}
		if($skip) {
			continue;
		}

		if(is_dir("$source/$name")) {
			if(!$recursive) {
				continue;
			}
			if(is_dir("$dest/$name")) {
				continue;
			}
			mkdir("$dest/$name", 0775, true);
			$this->copyFiles(
				"$source/$name",
				"$dest/$name",
				$skipWc,
				true);
		}
		else {
			copy("$source/$name", "$dest/$name");
		}
	}
}

}#