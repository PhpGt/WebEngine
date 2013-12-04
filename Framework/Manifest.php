<?php class Manifest {
/**
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */

public static $headElementDetails = [
	"Script" => [
		"TagName" => "script",
		"Type" => "Script",
		"SourceAttr" => "src",
		"ReqAttr" => [],
	],
	"Style" => [
		"TagName" => "link",
		"Type" => "Style",
		"SourceAttr" => "href",
		"ReqAttr" => ["rel" => "stylesheet"],
	],
];

/**
 * Static cuntion that returns an array of Manifest objects. The first element
 * is always a nameless manifest, representing the contents of the DOM head's
 * <link rel="stylesheet"> and <script src="..."> tags. The rest of the array
 * contains Manifest objects representing each <meta name="manifest"> tags in 
 * the DOM head (optional).
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
private $_md5;

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
	$attributes = [
		"script" => self::$headElementDetails["Script"],
		"link" => self::$headElementDetails["Style"],
	];
	
	$scriptLinkElements = $this->_domHead["script, link"];
	$this->_fileListArray = array("Script" => [], "Style" => []);

	foreach ($scriptLinkElements as $scriptLink) {
		$tag = $scriptLink->tagName;
		$attributeData = $attributes[$tag];

		$skipThisElement = false;

		foreach ($attributeData["ReqAttr"] as $key => $value) {
			if(!$scriptLink->hasAttribute($key)) {
				$skipThisElement = true;
				break;
			}
			if($scriptLink->hasAttribute($key)
			&& $scriptLink->getAttribute($key) != $value) {
				$skipThisElement = true;
				break;
			}
		}

		if(!$scriptLink->hasAttribute($attributeData["SourceAttr"])) {
			$skipThisElement = true;
		}

		if($skipThisElement) {
			continue;
		}

		$source = $scriptLink->getAttribute($attributeData["SourceAttr"]);
		$this->_fileListArray[$attributeData["Type"]][] = $source;
	}

	return $this->_fileListArray;
}

/**
 * Returns the MD5 hash of all files within both Script and Style .manifest.
 */
public function getMd5($forceRecalc = false) {
	if(!empty($this->_md5)
	&& !$forceRecalc) {
		return $this->_md5;
	}

	$md5 = "";
	$this->getFiles();

	foreach ($this->_fileListArray as $type => $fileList) {
		foreach ($this->_fileListArray[$type] as $filePath) {
			$filePathArray = array();
			if($filePath[0] == "/") {
				$filePathArray[] = APPROOT . $filePath;
				$filePathArray[] = GTROOT . $filePath;
			}
			else {
				$filePathArray[] = APPROOT . "/$type/$filePath";
				$filePathArray[] = GTROOT . "/$type/$filePath";
			}

			$found = false;
			foreach ($filePathArray as $fp) {
				if(file_exists($fp)) {
					$found = true;
					$processed = ClientSideCompiler::process($fp, null);
					$md5 .= md5($processed);
				}
			}
			
			if(!$found) {
				throw new Exception(
					"Manifest references file that does not exist (" 
						. $this->_name
						. " manifest, $filePath).");
			}
		}
	}

	$this->_md5 = md5($md5);
	return $this->_md5;
}

/**
 * 
 */
public function expandHead($type, $pathList, $domHead) {
	$myMeta = $domHead->xPath(
		".//meta[@name='manifest' and @content='{$this->_name}']");
	$elDetails = self::$headElementDetails[$type];
	if($myMeta->length == 0 
	&& is_null($this->_name)) {
		$tagName = $elDetails["TagName"];
		$sourceAttr = $elDetails["SourceAttr"];

		foreach ($pathList as $path) {
			$source = $path["Source"];
			$tagMatch = $domHead->xPath(
				"./{$tagName}[@{$sourceAttr}='{$source}']");
			$tagMatch->setAttribute($sourceAttr, $path["Destination"]);
		}
	}
	else {
		$publicPath = "/$type";
		if(empty($this->_name)) {
			$publicPath .= "/";
		}
		else {
			$publicPath .= "_" . $this->_name . "/";
		}

		foreach ($pathList as $path) {
			$destination = $path["Destination"];
			
			if(strpos($destination, "/$type") === 0) {
				$destination = substr($destination, strlen("/$type"));
			}

			$publicPath .= $destination;
			$publicPath = str_replace("//", "/", $publicPath);

			$el = $domHead->_dom->createElement($elDetails["TagName"]);
			$el->setAttribute($elDetails["SourceAttr"], $publicPath);

			foreach ($elDetails["ReqAttr"] as $key => $value) {
				$el->setAttribute($key, $value);
			}
			$domHead->insertBefore($el, $myMeta->node);
		}
		$myMeta->remove();
	}

}

}#