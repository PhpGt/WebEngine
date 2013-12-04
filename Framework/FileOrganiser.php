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
	$this->_wwwDir = APPROOT . "/www";
	$this->_manifestList = $manifestList;
}

public function organise($domHead) {
	$manifestCache = $this->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$assetCache = $this->checkCache(FileOrganiser::CACHETYPE_ASSET);

	// Store a reference to each file's source and destination.
	$manifestSourceDest = array(
		"Script" => [],
		"Style" => [],
	);

	// The DOM Head needs expanding to point to the correct location of the 
	// files within the www directory. This is necessary for these reasons:
	// 1) Some files, such as .scss, are renamed to .css during processing.
	// 2) Each individual manifest's files are coppied to self-contained www
	// directories. 
	foreach ($this->_manifestList as $manifest) {
		$manifestName = $manifest->getName();
		$md5 = $manifest->getMd5();

		$dirTypeArray = ["Script", "Style"];
		$fileList = $manifest->getFiles();

		foreach ($dirTypeArray as $dirType) {
			// Build up the www path to the containing directory for each 
			// manifest's individual Script and Style directories.
			$baseDir = $this->_wwwDir . "/$dirType";
			if(!empty($manifestName)) {
				$baseDir .= "_$manifestName";
			}

			// Obtain a list of relative paths for all files within the current
			// type.
			$processDestinations = ClientSideCompiler::getProcessDestinations(
				$fileList[$dirType]);
			$manifestSourceDest[$dirType] = $processDestinations;

			// Expand meta elements in DOM head to their actual files.
			$manifest->expandHead(
				$dirType, 
				$processDestinations,
				$domHead
			);
		}
	}

	if(!$manifestCache) {
		$this->organiseManifest($manifestSourceDest);
	}
	if(!$assetCache) {
		$this->organiseAsset();
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
public function checkCache($type = FileOrganiser::CACHETYPE_MANIFEST, 
$forceRecalc = false) {

	$isProduction = App_Config::isProduction();

	switch($type) {
	case FileOrganiser::CACHETYPE_MANIFEST:
		foreach ($this->_manifestList as $manifest) {
			$manifestName = $manifest->getName();
			$manifestMd5 = $manifest->getMd5($forceRecalc);

			if(is_null($manifestName)) {
				$manifestName = $manifestMd5;
			}
			$manifestCache = $this->_wwwDir . "/$manifestName.cache";
			if(!file_exists($manifestCache)) {
				return false;
			}
	
			if(!$isProduction) {
				$md5Cache = trim(file_get_contents($manifestCache));

				if($manifestMd5 !== $md5Cache) {
					return false;
				}
			}
		}

		return true;
		break;

	case FileOrganiser::CACHETYPE_ASSET;
		$isProduction = App_Config::isProduction();
		$cacheFile = $this->_wwwDir . "/asset.cache";
		if($isProduction && file_exists($cacheFile)) {
			return true;
		}

		$assetSourceDir = APPROOT . "/Asset";
		$assetList = $this->getAssetList($assetSourceDir);
		$md5 = "";
		foreach ($assetList as $asset) {
			$md5 .= md5_file("$assetSourceDir/$asset");
		}
		$md5 = md5($md5);
		$currentMd5 = "";
		if(file_exists($cacheFile)) {
			$currentMd5 = file_get_contents($cacheFile);
		}

		if($currentMd5 == $md5) {
			return true;
		}

		return false;
		break;
	}
}


/**
 * Performs a process & copy operation from source client-side directories into
 * www directory. Processes any special files such as scss, etc.
 */
public function organiseManifest(
$sourceDest = array("Script" => [], "Style" => [])) {

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
				$fileList[$dirType], $baseDir, $dirType, $sourceDest[$dirType]);
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
	$assetSourceDir = APPROOT . "/Asset";
	$assetWwwDir = APPROOT . "/www/Asset";
	$assetList = $this->getAssetList($assetSourceDir);
	$md5 = "";

	foreach ($assetList as $asset) {
		$sourcePath = "$assetSourceDir/$asset";
		$wwwPath = "$assetWwwDir/$asset";
		if(!is_dir(dirname($wwwPath))) {
			mkdir(dirname($wwwPath), 0775, true);
		}
		copy($sourcePath, $wwwPath);
		$md5 .= md5_file($sourcePath);
	}

	$md5 = md5($md5);
	file_put_contents("$assetWwwDir/asset.cache", $md5);
	return true;
}

private function getAssetList($dir) {
	$fileList = array();

	foreach ($iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir,
			RecursiveDirectoryIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST) as $item) {
		
		if($item->isDir()) {
			continue;
		}

		$fileList[] = $iterator->getSubPathName();
	}

	return $fileList;
}

/**
 * For each file referenced in each manifest, process the contents if
 * required, then write the processed contents to the public www directory.
 * After all files are processed.
 */
private function processCopy($fileList, $destDir, $type, $sourceDest = null) {
	// TODO: No need for array result any more.
	$result = array(
		"DestinationList" => [],
	);
	$sourceDir = APPROOT . "/$type";

	foreach ($fileList as $file) {
		// Because the dom head is already expanded by this point, the filename
		// stored in $file may not point to the source file - map it using
		// the $sourceDest array.
		if(!empty($sourceDest)) {
			foreach ($sourceDest as $sd) {
				if($sd["Destination"] == $file) {
					$file = $sd["Source"];
				}
			}
		}

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