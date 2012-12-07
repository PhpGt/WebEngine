<?php final class FileOrganiser {
/**
 * All files are stored outside of the webroot (www directory), so it is the job
 * of the FileOrganiser to copy the required files into the webroot when
 * required. The files may need to be minified and compiled before they are
 * copied.
 *
 * TODO: Implement these steps.
 * Steps made in this file:
 * 1) Loop over all files within Style and Script directories of APPROOT and
 * GTROOT, making a list of all files to be copied.
 * 2) For each .js and .css file, check the filemtime against the public
 * files of the same name, or the compiled Script.js/Style.css file. Remove file
 * from copy list if not changed.
 * 3) For each .scss file, check the filemtime against the public files of
 * the same name, with .css extension, or the compiled Style.css file, and
 * pre-process if necessary. Remove file from copy list if not changed.
 * 4) If there are files in the copy list (there is a change), empty the public
 * www directory and either copy the files or create a compiled file.
 */
public function __construct($config) {
	// For production sites, any un-compiled scripts that exist in the
	// web root should be removed.
	if($config->isClientCompiled()) {
		$this->removePublicFiles();
	}

	$this->copyFilesToPublic($config);
}

private function removePublicFiles() {
	$dir = APPROOT . DS . "www" . DS;
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

	$webroot = APPROOT . DS . "www" . DS;

	$copyDirArray = array(
		GTROOT  . DS . "Style" . DS . "Img"  . DS =>
			"Style" . DS . "Img",
		GTROOT  . DS . "Style" . DS . "Font" . DS =>
			"Style" . DS . "Font",
		APPROOT . DS . "Style" . DS . "Img"  . DS =>
			"Style" . DS . "Img",
		APPROOT . DS . "Style" . DS . "Font" . DS =>
			"Style" . DS . "Font",
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
			$this->copyFiles($source, $dest, true);
		}
	}
}

private function copyFiles($source, $dest, $recursive) {
	if(!is_dir($source)) {
		return;
	}

	$dh = opendir($source);
	@mkdir($dest, 0777, true);

	while(false !== ($name = readdir($dh)) ) {
		if($name[0] == ".") {
			continue;
		}

		// ALPHATODO:
		// TODO: There are a couple of errors surpressed here. A better
		// solution would be to detect if the directories are already
		// created, but this solution is here for the time being because
		// sometimes you *want* things to be overwritten.
		if(is_dir($source . DS . $name)) {
			if(!$recursive) {
				continue;
			}
			if(is_dir($dest . DS . $name)) {
				continue;
			}
			mkdir($dest . DS . $name, 0777, true);
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
			@chmod($dest . DS . $name, 0777);
			shell_exec("chown "
				. $own["name"] . " "
				. $dest . DS . $name);
		}
	}
}

}?>