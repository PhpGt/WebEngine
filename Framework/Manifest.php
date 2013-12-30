<?php class Manifest {
/**
 * https://github.com/g105b/PHP.Gt/wiki/structure~Manifest
 */

public static $elementDetails = [
	"Script" => [
		"TagName" => "script",
		"Extension" => "js",
		"EndTag" => true,
		"Source" => "src",
		"ReqAttr" => [],
	],
	"Style" => [
		"TagName" => "link",
		"Extension" => "css",
		"EndTag" => false,
		"Source" => "href",
		"ReqAttr" => ["rel" => "stylesheet"],
	],
];

private $_typeArray = ["Script", "Style"];
private $_domHead;
private $_fingerprint = null;
private $_pathArray = null;

public function __construct($domHead, $skipExpandDomHead = false) {
	$this->_domHead = $domHead;
	$this->expandMetaTags();

	if(!$skipExpandDomHead) {
		$this->_fingerprint = $this->getFingerprint();
		$this->expandDomHead();
	}
}

public function getDomHead() {
	return $this->_domHead;
}

/**
 * Returns a hash representing the contents of the DOM head. Only externally
 * loaded client-side assets will be represented in the hash - this includes
 * script elements with the src attribute, and link elements with the href and
 * rel=stylesheet attributes.
 *
 * @return string The hash representation of the current dom head's external
 * client-side assets.
 */
public function getFingerprint() {
	// End early if all work has already been done.
	if(!is_null($this->_fingerprint)
	&& !is_null($this->_pathArray)) {
		return $this->_fingerprint;
	}

	$fingerprint = "";
	
	$this->_pathArray = array();
	$elementList = $this->getAllHeadElements();
	foreach ($elementList as $element) {
		foreach (Manifest::$elementDetails as $type => $typeDetails) {
			// Locate the element's typeDetails:
			if(strtolower($typeDetails["TagName"])
			!= strtolower($element->tagName)) {
				continue;
			}

			// Skip elements that don't have the source attribute.
			if(!$element->hasAttribute($typeDetails["Source"])) {
				break;
			}
			// Skip elements that don't have all the required attributes.
			foreach ($typeDetails["ReqAttr"] as $key => $value) {
				if(!$element->hasAttribute($key)
				|| $element->getAttribute($key) != $value) {
					break 2;
				}
			}

			// At this point in the loop, this element should be used in the
			// fingerprinting process.
			$source = $element->getAttribute($typeDetails["Source"]);
			$fingerprint .= $source;
			$this->_pathArray[] = $source;
		}
	}

	$this->_fingerprint = md5($fingerprint);
	return $this->_fingerprint;
}

/**
 * Returns an array of file paths that the current dom head repressents. The
 * internally-stored array is built in getFingerprint method, so if the list
 * is null, it will call getFingerprint first.
 *
 * @return array Array of each file path represented in currently requested
 * dom head.
 */
public function getPathArray() {
	if(is_null($this->_pathArray)) {
		$this->getFingerprint();
	}

	return $this->_pathArray;
}

/**
 * Given a source path, inject the fingerprint to the Style or Script directory
 * name. This method acts as a simple map between source and destination files.
 *
 * @param $source string The public path to convert.
 * @return string The public path, with injected fingerprint.
 */
public function getFingerprintPath($source) {
	$fingerprint = $this->getFingerprint();
	$match = "/^\/(Script|Style)\//";
	$replace = "/\$1_$fingerprint/";

	return preg_replace($match, $replace, $source);
}

/**
 * Checks if there is a directory in the public www directory with the same name
 * as this manifest's fingerprint.
 */
public function isCacheValid() {
	$fingerprint = $this->getFingerprint();
	$minifiedDir = APPROOT . "/www/Min";
	if(App_Config::isClientCompiled()) {
		if(!is_dir($minifiedDir)) {
			die("nodir");
			return false;
		}
	}

	$fingerprintDirectoryArray = [
		APPROOT . "/www/Script_" . $fingerprint,
		APPROOT . "/www/Style_" . $fingerprint,
	];
	foreach ($fingerprintDirectoryArray as $fingerprintDirectory) {
		if(is_dir($fingerprintDirectory)) {
			return true;
		}
	}
	return false;
}

/**
 * Loops over all meta manifest tags in the dom head, outputs the matching
 * link and script tags, then removes the meta manifest tags.
 */
private function expandMetaTags() {
	$metaTagList = $this->_domHead["meta[name='manifest']"];

	foreach ($metaTagList as $metaTag) {
		if(!$metaTag->hasAttribute("content")) {
			throw new Exception(
				"Manifest tag in DOM head without content attribute.");
		}
		$name = $metaTag->getAttribute("content");

		// Obtain a list of all files represented by this meta tag.
		$typeFileList = $this->getFileList($name);
		foreach ($typeFileList as $type => $fileList) {
			$typeDetails = Manifest::$elementDetails[$type];

			// Loop over each file within the .manifest file...
			foreach ($fileList as $file) {
				// ... and output the element that matches.
				$element = $this->_domHead->_dom->createElement(
					$typeDetails["TagName"]);
				$element->setAttribute($typeDetails["Source"], $file);
				foreach ($typeDetails["ReqAttr"] as $key => $value) {
					$element->setAttribute($key, $value);
				}

				$this->_domHead->insertBefore($element, $metaTag);
			}
		}
	}

	$metaTagList->remove();
}

/**
 * Returns an associative array of file paths from within the .manifest file.
 */
private function getFileList($name) {
	$result = array();
	$manifestFileName = "$name.manifest";

	foreach ($this->_typeArray as $type) {
		$manifestFilePath = APPROOT . "/$type/$manifestFileName";
		if(!file_exists($manifestFilePath)) {
			continue;
		}

		$lines = file($manifestFilePath);
		foreach ($lines as $l) {
			$l = trim($l);
			if(empty($l)
			|| $l[0] == "#") {
				// Comment or empty line.
				continue;
			}

			if(substr($l, -1) == "*") {
				$noAsterisk = substr($l, 0, -1);
				$dirPath = APPROOT . $noAsterisk;
				$output = array();

				FileSystem::loopDir($dirPath, $output,
				function($item, $iterator, &$output) {
					if($item->isDir()) {
						return;
					}

					$output[] = $iterator->getSubPathname();
				});
				
				foreach ($output as $o) {
					$result[$type][] = $noAsterisk . $o;					
				}
			}
			else {
				$result[$type][] = $l;
			}
		}
	}

	return $result;
}

/**
 * Internally-used function to get a DomElCollection of all elements within the
 * DOM head that have the required tag names.
 */
private function getAllHeadElements() {
	$cssSelector = "";
	foreach (Manifest::$elementDetails as $type => $typeDetails) {
		if(!empty($cssSelector)) {
			$cssSelector .= ", ";
		}

		$cssSelector .= $typeDetails["TagName"];
	}

	return $this->_domHead[$cssSelector];
}

/**
 * Loops over all elements in the head, injects the fingerprint and removes
 * any processed-away file extensions to the source attributes.
 */
public function expandDomHead() {
	$elementList = $this->getAllHeadElements();
	$fingerprint = $this->getFingerprint();

	foreach ($elementList as $element) {
		foreach (Manifest::$elementDetails as $type => $typeDetails) {
			if(strtolower($typeDetails["TagName"])
			== strtolower($element->tagName)) {
				if(!$element->hasAttribute($typeDetails["Source"])) {
					continue;
				}
				$source = $element->getAttribute($typeDetails["Source"]);

				// Inject the fingerprint.
				$source = preg_replace(
					"/^\/{$type}\//", "/{$type}_$fingerprint/", $source);

				// Ensure the browser can understand this type of file.
				$source = ClientSideCompiler::renameSource($source);

				$element->setAttribute($typeDetails["Source"], $source);
			}
		}
	}
}

/**
 * When isClientCompiled is true, the dom head should have its contents replaced
 * with the minified version of client-side files.
 */
public function minifyDomHead() {
	// Find reference point to current head elements.
	foreach (self::$elementDetails as $type => $typeDetails) {
		$prevNode = null;
		$elementList = $this->_domHead[$typeDetails["TagName"]];

		foreach($elementList as $element) {

			foreach ($typeDetails["ReqAttr"] as $key => $value) {
				if(!$element->hasAttribute($key)) {
					continue 2;
				}
				if($element->getAttribute($key) != $value) {
					continue 2;
				}
			}

			if($element->hasAttribute($typeDetails["Source"])) {
				if(is_null($prevNode)) {
					$prevNode = $element->previousSibling;
				}

				$element->remove();
			}
			else {
				continue;
			}
		}

		$minSource = "/Min/"
			. $this->getFingerprint()
			. "."
			. $typeDetails["Extension"];

		$minElement = $this->_domHead->_dom->createElement(
			$typeDetails["TagName"],
			[$typeDetails["Source"] => $minSource]
		);
		foreach ($typeDetails["ReqAttr"] as $key => $value) {
			$minElement->setAttribute($key, $value);
		}

		$nextNode = null;
		if(!is_null($prevNode)) {
			$nextNode = $prevNode->nextSibling;
		}

		$this->_domHead->insertBefore($minElement, $nextNode);
	}
}

}#