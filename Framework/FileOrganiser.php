<?php final class FileOrganiser {
/**
 * TODO: Docs.
 * TODO: What about overriding Gt files with App files? (test)
 */
public function __construct($config) {
	// TODO: Check filemtime - it may be possible to check directories'
	// invalidation without saving a blank file somewhere.

	// For production sites, any un-compiled scripts that exist in the
	// web root should be removed.
	if($config->isClientCompiled()) {
		$this->removePublicFiles();
	}

	$this->copyFilesToPublic($config);
}

private function removePublicFiles() {
	$dir = APPROOT . DS . "Web" . DS;
	$fileArray = scanDir($dir);
	foreach ($fileArray as $file) {
		$pattern = "/.*(\.css|\.js)$/i";
		$match = array();
		if(preg_match($pattern, $file, $match) == 0) {
			continue;
		}
		$fileToDelete = $dir . $match[0];

		// Don't delete the compiled files.
		$skipFiles = array(
			"Script.js",
			"Style.css"
		);
		// Don't delete CSS files describing fonts.
		// ... Find the names of font files first.
		$fontDirArray = array(
			APPROOT . DS . "Style" . DS . "Font" . DS,
			GTROOT  . DS . "Style" . DS . "Font" . DS
		);
		foreach ($fontDirArray as $fontDir) {
			if(!is_dir($fontDir)) {
				continue;
			}

			$dh = opendir($fontDir);
			while(false !== ($fileName = readdir($dh)) )  {
				if(pathinfo($fontDir . $fileName,
				PATHINFO_EXTENSION) == "css") {
					// Add CSS files for fonts to the skipFiles array.
					$skipFiles[] = $fileName;
				}
			}
			closedir($dh);
		}

		foreach ($skipFiles as $skipFile) {
			if(strstr($fileToDelete, $skipFile)) {
				continue 2;
			}
		}

		unlink($fileToDelete);
	}
}

private function copyFilesToPublic($config) {
	// The order is vital here: some applications will overwrite the GT
	// files with their own, in which case they will be copied *over* the
	// originals in the public directory.

	$webroot = APPROOT . DS . "Web" . DS;

	$copyDirArray = array(
		GTROOT  . DS . "Style" . DS . "Img"  . DS =>
			"Style" . DS . "Img",
		GTROOT  . DS . "Style" . DS . "Font" . DS =>
			"Font",
		APPROOT . DS . "Style" . DS . "Img"  . DS =>
			"Style" . DS . "Img",
		APPROOT . DS . "Style" . DS . "Font" . DS =>
			"Font",
		APPROOT . DS . "Asset" . DS =>
			"Asset"
	);

	$copyNonProductionDirArray = array(
		GTROOT  . DS . "Script" . DS =>
			"",
		GTROOT  . DS . "Style"  . DS =>
			"",
		APPROOT . DS . "Script" . DS =>
			"",
		APPROOT . DS . "Style"  . DS =>
			""
	);

	foreach ($copyDirArray as $source => $dest) {
		$dest = $webroot . $dest;
		$this->copyFiles($source, $dest, true);
	}

	if(!$config->isClientCompiled() ) {
		foreach($copyNonProductionDirArray as $source => $dest) {
			$dest = $webroot . $dest;
			$this->copyFiles($source, $dest, false);
		}
	}
}

private function copyFiles($source, $dest, $recursive) {
	if(!is_dir($source)) {
		return;
	}

	$dh = opendir($source);
	@mkdir($dest, 0775, true);

	while(false !== ($name = readdir($dh)) ) {
		if($name[0] == ".") {
			continue;
		}

		// TODO: There are a couple of errors surpressed here. A better
		// solution would be to detect if the directories are already
		// created, but this solution is here for the time being because
		// sometimes you *want* things to be overwritten.
		if(is_dir($source . $name)) {
			if(!$recursive) {
				continue;
			}
			@mkdir($dest . DS . $name, 0775, true);
			$this->copyFiles(
				$source . DS . $name,
				$dest . DS . $name,
				true);
		}
		else {
			// TODO: File permissions are not getting set correctly.
			copy($source . DS . $name, $dest . DS . $name);
			$own = posix_getpwuid(fileOwner($source . DS . $name));
			
			// TODO: Had to surpress errors/warnings here after pulling 
			// repo on another workstation.
			@chmod($dest . DS . $name, 0775);
			shell_exec("chown "
				. $own["name"] . " "
				. $dest . DS . $name);
		}
	}
}

}?>