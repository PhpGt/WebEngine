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

public function __construct($name) {
	$this->_name = $name;
}

/**
 * Returns a list of all Script and Style files that are present within the
 * optional .manifest files.
 */
public function getFiles() {
	$fileListArray = [
		"Script" => array(),
		"Style" => array(),
	];

	foreach ($fileListArray as $type => $fileList) {
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

					$fileListArray[$type][] = $f;
				}
			}
			else {
				$fileListArray[$type][] = $l;				
			}

		}
	}

	return $fileListArray;
}

}#