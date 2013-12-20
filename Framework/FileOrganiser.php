<?php final class FileOrganiser {
private $_manifest;

public function __construct($manifest) {
	$this->_manifest = $manifest;
}

/**
 * A call to this metehod ensures that all client-side files within the www
 * directory are up-to-date with the source files.
 */
public function organise() {
	$logger = Log::get();

	$assetFilesCache = $this->isAssetFilesCacheValid();
	if(!$assetFilesCache) {
		$this->copyAssets();
	}

	if(!$this->isStyleFilesCacheValid()
	&& !$this->_manifest->isCacheValid()) {
		$this->flushScriptStyleCache();
		$this->processCopy();
		$this->copyStyleFiles();
	}

	$this->_manifest->expandDomHead();
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
	
}

/**
 * Checks that any file in either APPROOT/Style or GTROOT/Style has not changed
 * in the source directories, compared to the www/Style copy.
 *
 * @return bool True for valid cache, false for invalid.
 */
private function isStyleFilesCacheValid() {
	return false;
}

/**
 * Removes all cached Script & Style files from within www.
 */
private function flushScriptStyleCache() {

}

/**
 * Copies all non-.css files from APPROOT/Style and GTROOT/Style into the 
 * www/Style directory. This is necessary so that style assets such as images
 * and fonts can be referenced from within client-side files themselves.
 *
 * A hash file is left in the www directory, representing all source files.
 */
private function copyStyleFiles() {
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
		function($item, $iterator, $wwwStyleDir) {
			if($item->isDir()) {
				return;
			}
			$sourcePath = $item->getPathname();
			if(preg_match("/\..*css$/", $sourcePath)) {
				return;
			}

			$wwwPath = "$wwwStyleDir/" . $iterator->getSubpathname();

			if(!is_dir(dirname($wwwPath))) {
				mkdir(dirname($wwwPath), 0775, true);
			}
			copy($sourcePath, $wwwPath);
			return md5_file($sourcePath);
		});

		$md5Array = array_merge($md5Array, $outputArray);
	}
	
	foreach ($md5Array as $m) {
		$md5 .= $m;
	}
	$md5 = md5($md5);
	file_put_contents(APPROOT . "/www/StyleFiles.cache", $md5);
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