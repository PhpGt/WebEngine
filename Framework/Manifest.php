<?php class Manifest {
/**
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */

public static $headElementDetails = [
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
];

private static $headElementSourceMap = [
	"/\.scss$/" => ".css",
];
private static $headElementDestMap = [
	"/\.css$/" => ".scss",
];

/**
 * Rewrites the source attribute of a given DOM element according to the 
 * $headElementSourceMap lookup.
 */
public static function headElementRename($tag) {
	foreach (self::$headElementDetails as $tagName => $details) {
		if($details["TagName"] != $tag->tagName) {
			continue;
		}

		$source = $tag->getAttribute($details["SourceAttr"]);
		foreach (self::$headElementSourceMap as $match => $replacement) {
			if (preg_match($match, $source)) {
				$source = preg_replace($match, $replacement, $source);
				$tag->setAttribute($details["SourceAttr"],$source);
			}
		}
	}
}

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
private $_fileListArray = null;
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
public function getFiles($forceRecalc = false) {
	if($forceRecalc) {
		$this->_fileListArray = null;
	}

	// Allow caching the file list from previous calls.
	if(!is_null($this->_fileListArray)) {
		return $this->_fileListArray;
	}

	if(is_null($this->_name)) {
		return $this->getFilesFromHead();
	}

	$this->_fileListArray = [
		"Script" => array(),
		"Style" => array(),
	];

	foreach ($this->_fileListArray as $type => $fileList) {
		$dir = APPROOT . "/$type";

		// Manifest files are not required. If one is not found, continue.
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

			$filePathArray = array();

			// If absolute path is given, treat base dir as APP/GT root.
			if($l[0] == "/") {
				$filePathArray[] = APPROOT . $l;
				$filePathArray[] = GTROOT . $l;
			}
			// Otherwise treat base dir as Script/Style directory.
			else {
				$filePathArray[] = APPROOT . "/$type/$l";
				$filePathArray[] = GTROOT . "/$type/$l";
			}

			$found = false;

			// Look in all possible directories, throw exception if missing.
			foreach ($filePathArray as $fp) {
				if(substr($l, -1) == "*") {
					$starDir = "$dir/" . dirname($l);
					if(!is_dir($starDir)) {
						continue;
					}

					// If * has been given, add all files in directory.
					$innerFiles = scandir($starDir);
					foreach ($innerFiles as $f) {
						if($f[0] == ".") {
							continue;
						}

						$innerDir = substr($fp, 0, stripos($fp, "*"));
						$innerPath = $innerDir . $f;
						
						if(!file_exists($innerPath)) {
							continue;
						}
						
						$this->_fileListArray[$type][] = $innerPath;
						$found = true;
					}
				}
				else {
					if(file_exists($fp)) {
						$this->_fileListArray[$type][] = $fp;
						$found = true;
						break;
					}					
				}
			}

			if(!$found) {
				throw new Exception(
					"File within Manifest or DOM head does not exist: "
						. "$l");
			}
		}
	}

	return $this->_fileListArray;
}

/**
 * Like the getFiles() function, this function fills the internal fileListArray
 * with a list of all files represented by the manifest. The difference is that
 * this function builds its list of files up from the actual dom head elements.
 * Only to be used as an internal function, for when a manifest is nameless.
 */
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
		$type = array_search($attributeData, self::$headElementDetails);

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

		$publicSource = $scriptLink->getAttribute($attributeData["SourceAttr"]);
		$source = null;

		$sourceDirectoryArray = [
			APPROOT,
			GTROOT,
		];
		foreach ($sourceDirectoryArray as $sourceDirectory) {
			$sourcePath = $sourceDirectory . $publicSource;
			if(file_exists($sourcePath)) {
				$source = $sourcePath;
				break;
			}

			foreach(self::$headElementDestMap as $match => $replacement) {
				if(preg_match($match, $sourcePath)) {
					$replacedPath = preg_replace(
						$match, $replacement, $sourcePath);
					if(file_exists($replacedPath)) {
						$source = $replacedPath;
						break 2;
					}
				}
			}
		}

		$this->_fileListArray[$type][] = $source;
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
	$this->getFiles($forceRecalc);

	foreach ($this->_fileListArray as $type => $fileList) {

		foreach ($this->_fileListArray[$type] as $filePath) {
			if(!file_exists($filePath)) {
				throw new Exception(
					"Manifest references file that does not exist (" 
						. $this->_name
						. " manifest, $filePath).");
			}
				
			$processed = ClientSideCompiler::process($filePath, null);
			$md5 .= md5(trim($processed));
		}
	}

	$this->_md5 = md5($md5);
	return $this->_md5;
}

/**
 * A manifest can be represented by one or more meta tags in the dom head. This
 * function replaces the meta tag matching the manifest's name with the actual
 * script and link tags.
 *
 * For manifests that represent that _actual_ dom head, without a manifest
 * file, this function simply renames the file extension of particular paths 
 * that are to be processed (for example, /Script/Main.scss => /Script/Main.css)
 *
 * @param string $type Either 'Script' or 'Style', representing the type of 
 * manifest expansion to perform.
 * @param DomEl $domHead The head element in the current DOM.
 * @param string $wwwDir The output base directory within www root.
 */
public function expandHead($type, $domHead, $wwwDir) {
	$myMeta = $domHead->xPath(
		".//meta[@name='manifest' and @content='{$this->_name}']");


	$elDetails = self::$headElementDetails[$type];


	// For manifests that represent actual script/link elements in the head,
	// without using a .manifest file:
	if($myMeta->length == 0 
	&& is_null($this->_name)) {
		$tagName = $elDetails["TagName"];
		$sourceAttr = $elDetails["SourceAttr"];

		// Find all elements with required attributes:
		$tagMatch = $domHead[$tagName];
		foreach ($tagMatch as $tag) {
			foreach ($elDetails["ReqAttr"] as $key => $value) {
				if($tag->hasAttribute($key)
				&& $tag->getAttribute($key) == $value) {
					// Rename the source attribute according to lookup:
					self::headElementRename($tag);
				}
			}
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

		$fileList = $this->getFiles();
		foreach ($fileList[$type] as $file) {
			// Each path in $fileList is absolute on disk - need to strip off
			// the APPROOT or GTROOT prefix.

			if(strpos($file, APPROOT . "/$type/") === 0) {
				$file = substr($file, strlen(APPROOT . "/$type/"));
			}
			else if(strpos($file, GTROOT . "/$type/") === 0) {
				$file = substr($file, strlen(GTROOT . "/$type/"));
			}

			$publicPath .= $file;
			
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