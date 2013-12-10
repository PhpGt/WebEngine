<?php final class FileOrganiser {
/**
 * This class works closely with Manifest and ClientSideCompiler to ensure that
 * all source files are stored ouside of the webroot (www directory), but the
 * compiled or minified versions are copied correctly when required.
 */
const CACHETYPE_MANIFEST = 1;
const CACHETYPE_ASSET = 2;
const CACHETYPE_STYLEFILES = 4;

private $_wwwDir;
private $_manifestList;

public function __construct($manifestList) {
	$this->_wwwDir = APPROOT . "/www";
	$this->_manifestList = $manifestList;
}

public function organise($domHead) {
	$logger = Log::get();
	$manifestCache = $this->checkCache(FileOrganiser::CACHETYPE_MANIFEST);
	$assetCache = $this->checkCache(FileOrganiser::CACHETYPE_ASSET);
	$styleFilesCache = $this->checkCache(FileOrganiser::CACHETYPE_STYLEFILES);

	// The DOM Head needs expanding to point to the correct location of the 
	// files within the www directory. This is necessary for these reasons:
	// 1) Some files, such as .scss, are renamed to .css during processing.
	// 2) Each individual manifest's files are coppied to self-contained www
	// directories. 
	foreach ($this->_manifestList as $manifest) {
		$manifestName = $manifest->getName();

		$dirTypeArray = ["Script", "Style"];
		$fileList = $manifest->getFiles();

		foreach ($dirTypeArray as $dirType) {
			// Build up the www path to the containing directory for each 
			// manifest's individual Script and Style directories.
			$baseDir = $this->_wwwDir . "/$dirType";
			if(!empty($manifestName)) {
				$baseDir .= "_$manifestName";
			}

			// Expand meta elements in DOM head to their actual files.
			$manifest->expandHead(
				$dirType,
				$domHead,
				$baseDir
			);
		}
	}


	// Allow non-css files (such as images, icons, etc.) to be stored in the
	// Style directory.
	if(!$manifestCache
	|| !$styleFilesCache) {
		$logger->trace("Manifest/StyleFiles Cache invalid.");
		$this->organiseManifest();
		$this->organiseStyleFiles();
	}
	if(!$assetCache) {
		$logger->trace("Asset cache invalid.");
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
	$logger = Log::get();

	switch($type) {
	case FileOrganiser::CACHETYPE_MANIFEST:
		foreach ($this->_manifestList as $manifest) {
			// Getting the md5 of a manifest is expensive because the md5 has
			// to be calculated on the processed content.
			// The StyleFiles.cache file represents all unprocessed files, in
			// the APPROOT and GTROOT. If its modified time is later than that
			// of any source style file, it can be assumed no files have 
			// changed.
			$styleFilesCache = APPROOT . "/www/StyleFiles.cache";
			if(file_exists($styleFilesCache)) {
				$mtime_stylefiles = filemtime($styleFilesCache);
				$mtime_source = $this->getStyleMTime();

				if($mtime_source <= $mtime_stylefiles) {
					return true;
				}
			}

			$manifestName = $manifest->getName();
			if(empty($manifestName)) {
				$logger->trace("Getting manifest cache for DOM Head");
			}
			else {
				$logger->trace("Getting manifest cache for $manifestName");
			}
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

	case FileOrganiser::CACHETYPE_ASSET:
		$isProduction = App_Config::isProduction();
		$cacheFile = $this->_wwwDir . "/Asset.cache";
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
	case FileOrganiser::CACHETYPE_STYLEFILES:
		$styleDirectoryArray = array(
			APPROOT . "/Style",
			GTROOT . "/Style",
		);
		$styleFileArray = array();
		$hashFile = APPROOT . "/www/StyleFiles.cache";
		$isProduction = App_Config::isProduction();
		$md5 = "";

		if($isProduction && file_exists($hashFile)) {
			return true;
		}

		foreach ($styleDirectoryArray as $styleDirectory) {
			foreach ($iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($styleDirectory,
					RecursiveDirectoryIterator::SKIP_DOTS),
				RecursiveIteratorIterator::SELF_FIRST) as $item) {

				if($item->isDir()) {
					continue;
				}
				$pathName = $item->getPathName();

				// We want an md5 of *all* files...
				$md5 .= md5_file($pathName);
				// ... but only want to copy non-stylesheets.
				if(!preg_match("/\..?css$/", $pathName)) {
					$styleFileArray[] = $pathName;
				}
			}
		}

		$md5 = md5($md5);
		if(file_exists($hashFile)) {
			$hash = trim(file_get_contents($hashFile));
			if($hash == $md5) {
				return true;
			}
		}

		return false;
		break;
	}
}


/**
 * Performs a process & copy operation from source client-side directories into
 * www directory. Processes any special files such as scss, etc.
 */
public function organiseManifest() {
	// Remove old cache files:
	$skipFiles = ["StyleFiles.cache", "Asset.cache"];
	$files = scandir($this->_wwwDir);
	foreach ($files as $f) {
		$fp = $this->_wwwDir . "/$f";
		if($f[0] == "."
		|| is_dir($fp)
		|| in_array($f, $skipFiles)
		|| !preg_match("/\.cache$/", $f)) {
			continue;
		}

		unlink($fp);
	}

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
			$this->processCopy($fileList[$dirType], $baseDir, $dirType);
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
	file_put_contents(APPROOT . "/www/Asset.cache", $md5);
	return true;
}

/**
 * All stylesheet files within the source directory will end in css, including
 * those that are processed. This function copies all non-stylesheet files,
 * such as images, into the www/Style directory and stores an md5 hash in
 * www/StyleFiles.cache
 */
private function organiseStyleFiles() {
	$styleDirectoryArray = array(
		APPROOT . "/Style",
		GTROOT . "/Style",
	);
	$styleFileArray = array();
	$hashFile = APPROOT . "/www/StyleFiles.cache";
	$md5 = "";

	foreach ($styleDirectoryArray as $styleDirectory) {
		foreach ($iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($styleDirectory,
				RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST) as $item) {

			if($item->isDir()) {
				continue;
			}
			$pathName = $item->getPathName();

			// We want an md5 of *all* files...
			$md5 .= md5_file($pathName);
			// ... but only want to copy non-stylesheets.
			if(!preg_match("/\..?css$/", $pathName)) {
				$styleFileArray[] = $pathName;
			}
		}
	}

	foreach ($styleFileArray as $styleFile) {
		if(strpos($styleFile, APPROOT) === 0) {
			$destination = substr($styleFile, strlen(APPROOT));
		}
		else if(strpos($styleFile, GTROOT) === 0) {
			$destination = substr($styleFile, strlen(GTROOT));
		}
		else {
			throw new Exception("Source style file can't be found: $styleFile");
		}
		$destination = APPROOT . "/www" . $destination;

		if(!is_dir(dirname($destination))) {
			mkdir(dirname($destination), 0775, true);
		}
		copy($styleFile, $destination);
	}

	$md5 = md5($md5);
	file_put_contents($hashFile, $md5);
	return true;
}

/**
 * Gets the latest time any files within the APPROOT/Style or GTROOT/Style
 * directories have been modified.
 */
private function getStyleMTime() {
	$styleDirectoryArray = array(
		APPROOT . "/Style",
		GTROOT . "/Style",
	);
	$mtimeLatest = 0;

	foreach ($styleDirectoryArray as $styleDirectory) {
		foreach ($iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($styleDirectory,
				RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST) as $item) {

			if($item->isDir()) {
				continue;
			}

			$mtime = filemtime($item->getPathName());
			if($mtime > $mtimeLatest) {
				$mtimeLatest = $mtime;
			}
		}
	}

	return $mtimeLatest;
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
 * required, then write the processed contents into the public www directory.
 *
 * @param array $fileList A list of script/style files, already matched to an
 * existing source file on disk. Source file may be in APPROOT or GTROOT, but
 * may have a processed file extension (which will need re-mapping to original).
 * @param string $destDir Full absolute path of the directory to output the 
 * processed file to. This is a www/Script or www/Style directory, but may
 * contain the name of the manifest.
 * @param string $type Either "Script" or "Style".
 */
private function processCopy($fileList, $destDir, $type) {
	foreach ($fileList as $file) {
		$processed = null;

		if(!file_exists($file)) {
			$found = false;
			// Because the dom head is already expanded by this point, the 
			// filename stored in $file may not point to the source file - 
			// attempt to map it using the $headElementDestMap array.
			foreach (Manifest::$headElementDestMap as $match => $replacement) {
				if(preg_match($match, $file)) {
					$fileReplaced = preg_replace($match, $replacement, $file);

					if(file_exists($file)) {
						$found = true;
						$file = $fileReplaced;
					}
				}
			}
			if(!$found) {
				throw new Exception("File Organiser's file can't be processed: "
					. $file);				
			}
		}

		$fileContents = file_get_contents($file);
		$processed = ClientSideCompiler::process($file);	

		// $destDir may contain the name of the manifest in the directory name.
		// $file is the absolute path to the source file.
		// Manipulate $destDir and $file to point to the absolute path to the 
		// public www file.
		$relativeFile = "";
		if(strpos($file, APPROOT) === 0) {
			$rootAndType = APPROOT . "/$type";
		}
		else if(strpos($file, GTROOT) === 0) {
			$rootAndType = GTROOT . "/$type";
		}
		else {
			throw new Exception("File Organiser can't find processed file: "
				. $file);
		}
		$relativeFile = substr($file, strlen($rootAndType));

		$destinationPath = $destDir . $relativeFile;

		if(!is_dir(dirname($destinationPath))) {
			mkdir(dirname($destinationPath), 0775, true);
		}

		file_put_contents(
			$destinationPath, 
			$processed
		);
	}
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