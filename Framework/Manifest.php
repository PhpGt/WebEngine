<?php class Manifest {
/**
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */
public static function getList($domHead) {
	$list = array();

	// When no manifests are mentioned in the dom head, a non-named manifest
	// should be used, representing the dom head in its current state.
	// This nameless Manifest will also be present when there *are* other
	// Manifests in the head, as to contain and represent stray link/style
	// elements.
	$list[] = new Manifest($domHead);

	$metaList = $domHead->xPath(".//meta[@name='manifest']");
	foreach ($metaList as $meta) {
		$manifestName = $meta->getAttribute("content");
		$list[] = new Manifest($manifestName);
	}

	return $list;
}

private $_name;
private $_domHead;
private $_fileListArray;

/**
 * The Manifest constructor can take its name as a string, or the DOM head to
 * represent as a nameless Manifest.
 */
public function __construct($nameOrDomHead) {
	if(is_string($nameOrDomHead)) {
		$this->_name = $nameOrDomHead;		
	}
	else if($nameOrDomHead instanceof DomEl) {
		$this->_name = null;
		$this->_domHead = $nameOrDomHead;
	}
	else {
		throw new Exception("Manifest constructed with incorrect parameters");
	}
}

public function getName() {
	return $this->_name;
}

/**
 * Returns a list of all Script and Style files that are present within the
 * optional .manifest files.
 */
public function getFiles() {
	if(is_null($this->_name)) {
		return $this->getFilesFromHead();
	}

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
				if(!file_exists("$dir/$l")) {
					throw new Exception(
						"File within Manifest or DOM head does not exist: "
						. "$dir/$l");
				}
				$this->_fileListArray[$type][] = $l;				
			}

		}
	}

	return $this->_fileListArray;
}

private function getFilesFromHead() {
	$scriptLinkElements = $this->_domHead["script, link"];
	$attributes = [
		"script" => [
			"Type" => "Script",
			"Source" => "src",
			"Required" => []
		],
		"link" => [
			"Type" => "Style",
			"Source" => "href",
			"Required" => ["rel" => "stylesheet"]
		],
	];
	$this->_fileListArray = array("Script" => [], "Style" => []);

	foreach ($scriptLinkElements as $scriptLink) {
		$tag = $scriptLink->tagName;
		$attributeData = $attributes[$tag];

		$skipThisElement = false;

		foreach ($attributeData["Required"] as $key => $value) {
			if(!$scriptLink->hasAttribute($key)) {
				$skipThisElement = true;
				break;
			}
		}

		if(!$scriptLink->hasAttribute($attributeData["Source"])) {
			$skipThisElement = true;
		}

		if($skipThisElement) {
			continue;
		}

		$source = $scriptLink->getAttribute($attributeData["Source"]);
		$this->_fileListArray[$attributeData["Type"]][] = $source;
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
	if($myMeta->length == 0 
	&& is_null($this->_name)) {
		// Remove all existing script/link tags from head on this occasion.
		foreach ($elementType as $type => $typeData) {
			$elementList = $domHead[$typeData["TagName"]];
			foreach ($elementList as $el) {
				$doNotRemove = false;
				foreach ($typeData["ReqAttr"] as $key => $value) {
					if(!$el->hasAttribute($key)) {
						$doNotRemove = true;
					}
				}
				if(!$el->hasAttribute($typeData["SourceAttr"])) {
					$doNotRemove = true;
				}

				if(!$doNotRemove) {
					$el->remove();
				}
			}
		}
	}

	foreach ($destinationList as $destination) {
		$publicPath = "/$type";
		if(empty($this->_name)) {
			$publicPath .= "/";
		}
		else {
			$publicPath .= "_" . $this->_name . "/";
		}
		$publicPath .= $destination;
		$publicPath = str_replace("//", "/", $publicPath);

		$el = $domHead->_dom->createElement($elementType[$type]["TagName"]);
		$el->setAttribute($elementType[$type]["SourceAttr"], $publicPath);

		foreach ($elementType[$type]["ReqAttr"] as $key => $value) {
			$el->setAttribute($key, $value);
		}
		$domHead->appendChild($el);
	}
	$myMeta->remove();
}

}#