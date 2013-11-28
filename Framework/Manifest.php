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

public function getName() {
	return $this->_name;
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
	$this->getFiles();

	foreach ($this->_fileListArray as $type => $fileList) {
		foreach ($this->_fileListArray[$type] as $filePath) {
			$filePath = APPROOT . "/$type/$filePath";

			if(!file_exists($filePath)) {
				throw new Exception(
					"Manifest references file that does not exist (" 
						. $this->_name
						. " manifest, $filePath).");
			}

			$md5 .= md5_file($filePath);
		}
	}

	return md5($md5);
}

public function expandHead($type, $destinationList, $domHead) {
	$elementType = array(
		"Script" => [
			"TagName" => "script",
			"SourceAttr" => "src",
			"ReqAttr" => [],
		],
		"Style" => [
			"TagName" => "link",
			"SourceAttr" => "href",
			"ReqAttr" => ["rel" => "stylesheet"],
		],
	);

	$myMeta = $domHead->xPath(
		".//meta[@name='manifest' and @content='{$this->_name}']");

	foreach ($destinationList as $destination) {
		$publicPath = "/$type";
		if(empty($this->_name)) {
			$publicPath .= "/";
		}
		else {
			$publicPath .= "_" . $this->_name . "/";
		}
		$publicPath .= $destination;

		$el = $domHead->_dom->createElement($elementType[$type]["TagName"]);
		$el->setAttribute($elementType[$type]["SourceAttr"], $publicPath);
		foreach ($elementType[$type]["ReqAttr"] as $key => $value) {
			$el->setAttribute($key, $value);
		}
		$domHead->appendChild($el);
	}
	$myMeta->remove();
	// var_dump($publicPath, $destinationList);die();
}

}#