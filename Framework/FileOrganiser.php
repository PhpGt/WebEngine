<?php final class FileOrganiser {
/**
 * This class works closely with Manifest and ClientSideCompiler to ensure that
 * all source files are stored ouside of the webroot (www directory), but the
 * compiled or minified versions are copied correctly when required.
 */
const CACHETYPE_MANIFEST = 1;
const CACHETYPE_ASSET = 2;

private $_wwwDir;
private $_manifestList;

public function __construct($manifestList) {
	// TODO: Got to know about the manifest here!
	$this->_wwwDir = APPROOT . "/www";
	$this->_manifestList = $manifestList;
}

public function organise($domHead) {
	$manifestCache = $this->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$assetCache = $this->checkCache(FileOrganiser::CACHETYPE_ASSET);

	foreach ($this->_manifestList as $manifest) {
		$manifestName = $manifest->getName();
		$dirTypeArray = ["Script", "Style"];
		$fileList = $manifest->getFiles();

		foreach ($dirTypeArray as $dirType) {
			$baseDir = $this->_wwwDir . "/$dirType";

			if(!empty($manifestName)) {
				$baseDir .= "_$manifestName";
			}

			$processDestinations = $this->getProcessDestinations(
				$fileList[$dirType]);

			// Expand meta elements in DOM head to their actual files.
			$manifest->expandHead(
				$dirType, 
				$processDestinations,
				$domHead
			);
		}		
	}

	if(!$manifestCache) {
		$this->organiseManifest($domHead);
	}
	if(!$assetCache) {
		$this->organiseAsset($domHead);
	}

	return true;
}
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
			if(is_null($manifestName)) {
				$manifestName = $manifest->getMd5();
			}
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
			if(is_null($manifestName)) {
				$manifestName = $manifest->getMd5();
			}
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
		return false;
		break;
	}
}


/**
 * Performs a process & copy operation from source client-side directories into
 * www directory. Processes any special files such as scss, etc.
 */
public function organiseManifest($domHead) {
	foreach ($this->_manifestList as $manifest) {
		$hash = $manifest->getMd5();

		$manifestName = $manifest->getName();
		$dirTypeArray = ["Script", "Style"];
		$fileList = $manifest->getFiles();

		foreach ($dirTypeArray as $dirType) {
			$baseDir = $this->_wwwDir . "/$dirType";

			if(!empty($manifestName)) {
				$baseDir .= "_$manifestName";
			}
			
			$this->recursiveRemove($baseDir);
			$processResult = $this->processCopy(
				$fileList[$dirType], $baseDir, $dirType);
		}

		$md5File = (empty($manifestName))
			? $this->_wwwDir . "/$hash.cache"
			: $this->_wwwDir . "/$manifestName.cache";
		file_put_contents($md5File, $hash);
	}
}

/**
 * Removes and re-copies all Asset files.
 */
public function organiseAsset() {
	// TODO: Copy assets.
	return true;
}

private function getProcessedPath($file) {
	throw new Exception("NYI!");
}

/**
 * For each file referenced in each manifest, process the contents if
 * required, then write the processed contents to the public www directory.
 * After all files are processed.
 */
private function processCopy($fileList, $destDir, $type) {
	// TODO: No need for array result any more.
	$result = array(
		"DestinationList" => [],
	);
	$sourceDir = APPROOT . "/$type";

	foreach ($fileList as $file) {
		if($file[0] == "/") {
			$sourcePath = APPROOT . "$file";
		}
		else {
			$sourcePath = "$sourceDir/$file";
		}

		if(!file_exists($sourcePath)) {
			continue;
		}

		$fileContents = file_get_contents($sourcePath);
		$processed = ClientSideCompiler::process($sourcePath, $file);

		$result["DestinationList"][] = $processed["Destination"];

		if($processed["Destination"][0] == "/") {
			$destinationPath = substr($destDir, 0, stripos($destDir, "/$type"))
				. $processed["Destination"];
		}
		else {
			$destinationPath = $destDir . "/" . $processed["Destination"];			
		}

		if(!is_dir(dirname($destinationPath))) {
			mkdir(dirname($destinationPath), 0775, true);
		}

		file_put_contents(
			$destinationPath, 
			$processed["Contents"]
		);
	}

	return $result;
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