<?php final class FileOrganiser {
private $_manifest;
private $_styleFileCache;

public function __construct($manifest) {
	$this->_manifest = $manifest;
	$this->_styleFileCache = APPROOT . "/www/StyleFiles.cache";
}

/**
 * A call to this metehod ensures that all client-side files within the www
 * directory are up-to-date with the source files.
 *
 * @return bool True if any copying was made, false if all caches were valid.
 */
public function organise() {
	$copyingDone = false;
	$logger = Log::get();

	$assetFilesCache = $this->isAssetFilesCacheValid();
	if(!$assetFilesCache) {
		$this->copyAssets();
	}

	$styleFilesCacheValid = $this->isStyleFilesCacheValid();
	if(!$styleFilesCacheValid) {
		$this->flushCache();
		$this->organiseStyleFiles();
		$copyingDone = true;
	}
	
	$manifestCacheValid = $this->_manifest->isCacheValid();
	if(!$manifestCacheValid) {
		$this->processCopy();
		$copyingDone = true;
	}

	$this->_manifest->expandDomHead();

	return $copyingDone;
}

/**
 * Checks the asset's cache file against source asset files for cache validity.
 * If all assets have been copied and are up to date, return true.
 *
 * @return bool True for valid cache, false for invalid.
 */
public function isAssetFilesCacheValid() {
	$assetWwwDir = APPROOT . "/www/Asset";
	if(!is_dir($assetWwwDir)) {
		return false;
	}
}

/**
 * Performs the recursive copy process for all files in the source asset 
 * directory.
 */
private function copyAssets() {
	$assetDir = APPROOT . "/Asset";
	$assetWwwDir = APPROOT . "/www/Asset";

	FileSystem::copy($assetDir, $assetWwwDir);
}

/**
 * Checks that any file in either APPROOT/Style or GTROOT/Style has not changed
 * in the source directories, compared to the www/Style copy.
 *
 * @return bool True for valid cache, false for invalid.
 */
public function isStyleFilesCacheValid() {
	if(!is_file($this->_styleFileCache)) {
		return false;
	}

	$currentMd5 = trim(file_get_contents($this->_styleFileCache));
	$md5 = $this->organiseStyleFiles(true);

	return $currentMd5 === $md5;
}

/**
 * Removes all cached Script & Style files from within www.
 */
private function flushCache() {
	if(file_exists($this->_styleFileCache)) {
		unlink($this->_styleFileCache);
	}

	$globArray = [
		APPROOT . "/www/Script*",
		APPROOT . "/www/Style*",
	];
	foreach ($globArray as $glob) {
		$dirArray = glob($glob);

		foreach ($dirArray as $dir) {
			FileSystem::remove($dir);
		}
	}
}

/**
 * Copies all non-.css files from APPROOT/Style and GTROOT/Style into the 
 * www/Style directory. This is necessary so that style assets such as images
 * and fonts can be referenced from within client-side files themselves.
 *
 * A hash file is left in the www directory, representing all source files.
 *
 * If dryRun is true, the copy action will be skipped, allowing for a check of
 * the md5 representing the directories.
 *
 * @param $dryRun bool Optional. True to skip copy action.
 * @return string The md5 representation of source Style directories.
 */
private function organiseStyleFiles($dryRun = false) {
	$dirArray = [
		APPROOT . "/Style",
		GTROOT . "/Style",
	];
	$wwwStyleDir = APPROOT . "/www/Style";

	$md5Array = array();
	$md5 = "";

	foreach ($dirArray as $dir) {
		if(!is_dir($dir)) {
			continue;
		}

		$outputArray = FileSystem::loopDir($dir, $wwwStyleDir, 
		[$this, "iterateStyleFiles"], !$dryRun);

		$md5Array = array_merge($md5Array, $outputArray);
	}
	
	foreach ($md5Array as $m) {
		$md5 .= $m;
	}
	$md5 = md5($md5);

	if(!$dryRun) {
		file_put_contents($this->_styleFileCache, $md5);
	}

	return $md5;
}

public function iterateStyleFiles($item, $iterator, $wwwStyleDir, $doCopy) {
	if($item->isDir()) {
		return;
	}

	$sourcePath = $item->getPathname();
	$md5 = md5_file($sourcePath);

	if(!preg_match("/\..*css$/", $sourcePath)) {
		$wwwPath = "$wwwStyleDir/" . $iterator->getSubpathname();

		if($doCopy) {
			if(!is_dir(dirname($wwwPath))) {
				mkdir(dirname($wwwPath), 0775, true);
			}
			copy($sourcePath, $wwwPath);				
		}
	}

	return $md5;
}

/**
 * Copies all files referenced by the manifest to their public www location,
 * while at the same time processing their contents using the 
 * ClientSideCompiler.
 */
private function processCopy() {
	$manifestPathArray = $this->_manifest->getPathArray();

	foreach ($manifestPathArray as $source) {
		// Allow referenced file to exist in either APPROOT or GTROOT, while
		// prefering the APPROOT's version if file exists in both locations.
		$sourcePathArray = [
			APPROOT . $source,
			GTROOT . $source,
		];
		$sourcePath = null;
		foreach ($sourcePathArray as $sp) {
			if(file_exists($sp)) {
				$sourcePath = $sp;
				break;
			}
		}
		// If sourcePath is null at this point, the source file can't be found.
		// Do not throw an exception though - treat it as it would in a static
		// website; the browser will show a 404 error in the console.
		if(is_null($sourcePath)) {
			continue;
		}

		$destination = $this->_manifest->getFingerprintPath($source);
		$destinationPath = APPROOT . "/www" . $destination;
		$destinationPath = ClientSideCompiler::renameSource($destinationPath);

		$processed = ClientSideCompiler::process($sourcePath);
		if(!is_dir(dirname($destinationPath))) {
			mkdir(dirname($destinationPath), 0775, true);
		}
		file_put_contents($destinationPath, $processed);
	}
}

}#