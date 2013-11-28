<?php class Manifest {
/**
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */
public static function getList($domHead) {
	$list = array();

	$metaList = $domHead->xPath(".//meta[@name='manifest']");
	foreach ($metaList as $meta) {
		$manifestName = $meta->getAttribute("content");
		$list[] = new Manifest($manifestName);
	}

	return $list;
}

private $_name;
private $_fileListArray;

public function __construct($name) {
	$this->_name = $name;
}

/**
 * Returns a list of all Script and Style files that are present within the
 * optional .manifest files.
 */
public function getFiles() {
	$this->_fileListArray = [
		"Script" => array(),
		"Style" => array(),
	];

	foreach ($this->_fileListArray as $type => $fileList) {
		$dir = APPROOT . "/$type";
		$mfFile = "$dir/" . $this->_name . ".manifest";

		if(!is_file($mfFile)) {
			continue;
		}
		$lines = file($mfFile);
		foreach ($lines as $l) {
			// Skip empty or comment lines.
			$l = trim($l);
			if(empty($l)
			|| $l[0] == "#") {
				continue;
			}

			if(substr($l, -1) == "*") {
				// If * has been given, add all files in directory.
				$innerFiles = scandir("$dir/" . dirname($l));
				foreach ($innerFiles as $f) {
					if($f[0] == ".") {
						continue;
					}

					$lf = substr($l, 0, stripos($l, "*"));
					$this->_fileListArray[$type][] = $lf . $f;
				}
			}
			else {
				$this->_fileListArray[$type][] = $l;				
			}

		}
	}

	return $this->_fileListArray;
}

/**
 * Returns the MD5 hash of all files within both Script and Style .manifest.
 */
public function getMd5() {
	$md5 = "";
	if(empty($this->_fileListArray)) {
		$this->getFiles();
	}
	foreach ($this->_fileListArray as $type => $fileList) {
		foreach ($this->_fileListArray[$type] as $filePath) {
			$filePath = APPROOT . "/$type/$filePath";

			$md5 .= md5_file($filePath);
		}
	}

	return md5($md5);
}

}#