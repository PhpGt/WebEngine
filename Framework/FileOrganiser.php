<?php final class FileOrganiser {
/**
 * This class works closely with Manifest and ClientSideCompiler to ensure that
 * all source files are stored ouside of the webroot (www directory), but the
 * compiled or minified versions are copied correctly when required.
 */
const CACHETYPE_MANIFEST = 1;
const CACHETYPE_ASSET = 2;

public function __construct($manifestList) {
	// TODO: Got to know about the manifest here!
	$this->_wwwDir = APPROOT . "/www";
	$this->_manifestList = $manifestList;
}

private $_wwwDir;
private $_manifestList;

/**
 * Checks if all manifest files are already copied to the www directory.
 * For each Manifest, if MD5 cache file exists, in production treat that as 
 * valid cache. When not in production, read MD5 cache and compare to source
 * directory contents. If MD5s differ, cache is invalid.
 *
 * Returns true for valid cache, false for invalid cache.
 */
public function checkCache($type = FileOrganiser::CACHETYPE_MANIFEST) {
	switch($type) {
	case FileOrganiser::CACHETYPE_MANIFEST:
		foreach ($this->_manifestList as $manifest) {
			$manifestName = $manifest->getName();
			$manifestCache = $this->_wwwDir . "/$manifestName.cache";
			if(!file_exists($manifestCache)) {
				return false;
			}
		}

		// All manifest cache files exist so far.
		if(App_Config::isProduction()) {
			return true;
		}

		// Need to check integrity of cache files.
		foreach ($this->_manifestList as $manifest) {
			$manifestName = $manifest->getName();
			$manifestCache = $this->_wwwDir . "/$manifestName.cache";
			$md5Cache = trim(file_get_contents($manifestCache));
			$manifestMd5 = $manifest->getMd5();

			if($manifestMd5 !== $md5Cache) {
				return false;
			}
		}

		return true;
		break;

	case FileOrganiser::CACHETYPE_ASSET;
		break;
	}
}

public function organise() {
	$manifestCache = $this->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$assetCache = $this->checkCache(FileOrganiser::CACHETYPE_ASSET);

	if(!$manifestCache) {
		$this->organiseManifest();
	}
	if(!$assetCache) {
		$this->organiseAsset();
	}
}

/**
 * Performs a process & copy operation from source client-side directories into
 * www directory. Processes any special files such as scss, etc.
 */
public function organiseManifest() {
	foreach ($this->_manifestList as $manifest) {
		$manifestName = $manifest->getName();
		$dirTypeArray = ["Script", "Style"];
		$fileList = $manifest->getFiles();
		$md5 = "";

		foreach ($dirTypeArray as $dirType) {
			$baseDir = $this->_wwwDir . "/$dirType";

			if(!empty($manifestName)) {
				$baseDir .= "_$manifestName";
			}
			
			$this->recursiveRemove($baseDir);
			$md5 .= $this->processCopy($fileList[$dirType], $baseDir, $dirType);
		}

		$md5File = $this->_wwwDir . "/$manifestName.cache";
		file_put_contents($md5File, md5($md5));
	}
}

/**
 * Removes and re-copies all Asset files.
 */
public function organiseAsset() {
	// TODO: Copy assets.
	return true;
}

/**
 * For each file referenced in each manifest, process the contents if
 * required, then write the processed contents to the public www directory.
 * After all files are processed, return an md5 hash of the *source* files,
 * to allow for only processing and copying when the source files change.
 */
private function processCopy($fileList, $destDir, $type) {
	$md5 = "";
	$sourceDir = APPROOT . "/$type";

	foreach ($fileList as $file) {
		$sourcePath = "$sourceDir/$file";
		if(!file_exists($sourcePath)) {
			continue;
		}

		$md5 .= md5_file($sourcePath);
		$fileContents = file_get_contents($sourcePath);
		$processed = ClientSideCompiler::process($sourcePath, $file);

		$destinationPath = $destDir . "/" . $processed["Destination"];
		if(!is_dir(dirname($destinationPath))) {
			mkdir(dirname($destinationPath), 0775, true);
		}

		file_put_contents(
			$destinationPath, 
			$processed["Contents"]
		);
	}

	return $md5;
}

/**
 * Removes the given directory and all of its contents.
 */
private function recursiveRemove($baseDir) {
	if(!is_dir($baseDir)) {
		return true;
	}

	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($baseDir, 
			RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST) as $item) {
			$subPath = $iterator->getSubPathName();

			if($item->isDir()) {
				rmdir("$baseDir/$subPath");
			}
			else {
				unlink("$baseDir/$subPath");
			}
	}

	rmdir($baseDir);
	return true;
}
}#