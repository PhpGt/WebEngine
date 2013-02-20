<?php final class Response {
/**
 * Deals with all view buffering and rendering, and provides a mechanism for the
 * Dispatcher to execute functions on all instantiated PageCode and PageTool 
 * objects. The Response object will be skipped if application cache is enabled,
 * and the cache is valdid. Rather, the Request object will simply serve the
 * cached response.
 */
private $_buffer = "";
private $_api = null;
private $_pageCode = null;
private $_pageCodeCommon = array();

private $_pageCodeStop;

public function __construct($request) {
	header("Content-Type: {$request->contentType}; charset=utf-8");
	header("X-Powered-By: PHP.Gt Version " . VER);

	if(EXT === "json") {
		$this->_api = $request->api;
		return;
	}

	$this->_pageCode = $request->pageCode;
	$this->_pageCodeCommon = $request->pageCodeCommon;
	$this->_pageCodeStop = &$request->pageCodeStop;
	ob_start();
	// Buffer current PageView and optional header/footer.
	$mtimeHeader = $this->bufferPageView("Header");
	$mtimeView = $this->bufferPageView();
	if($mtimeView === false) {
		throw new HttpError(404);
	}
	$mtimeFooter = $this->bufferPageView("Footer");

	$mtimeMax = max($mtimeHeader, $mtimeView, $mtimeFooter);
	if(empty($_SESSION["PhpGt_Cache"])) {
		$_SESSION["PhpGt_Cache"] = array();
	}
	$_SESSION["PhpGt_Cache"]["PageView_mtime"] = $mtimeMax;

	$this->storeBuffer();

	return;
}

/**
* Called by the dispatcher in order, the passed in parameter is the name of
* a function on the currently-loaded PageCode.
* @param string $name Name of the PageCode function to call.
* @param mixed $args Zero or more parameters to pass to the named function.
*/
public function dispatch($name, $parameter = null) {
	// There may or may not be a PageCode or Common PageCode.
	// Build array of objects to dispatch to.
	$dispatchArray = array();
	if(!empty($this->_pageCodeCommon)) {
		foreach ($this->_pageCodeCommon as $pageCode) {
			$dispatchArray[] = $pageCode;
		}
	}
	if(!is_null($this->_pageCode)) {
		$dispatchArray[] = $this->_pageCode;
	}
	if(!is_null($this->_api)) {
		$dispatchArray[] = $this->_api;
	}

	$args = func_get_args();
	array_shift($args);

	$result = null;
	// Call method, if it exists, on each existant PageCode.
	foreach($dispatchArray as $dispatchTo) {
		if(method_exists($dispatchTo, $name)) {
				if(!($dispatchTo instanceof PageCode 
				&& $this->_pageCodeStop) ) {
					$result = call_user_func_array(
						array($dispatchTo, $name),
						$args
					);
				}
		}
	}

	return $result;
}

/**
 * Creates and executes all PageTools assigned by current PageCode.
 */
public function executePageTools($pageToolArray, $api, $dom, $template) {
	$toolPathArray = array(
		APPROOT . DS . "PageTool",
		GTROOT . DS . "PageTool"
	);
	if(empty($pageToolArray)) {
		return;
	}
	foreach ($pageToolArray as $tool) {
		$tool = ucfirst($tool);
		$toolFile = $tool . ".tool.php";
		$toolClass = $tool . "_PageTool";
		foreach ($toolPathArray as $path) {
			if(!is_dir($path)) {
				continue;
			}
			
			if(file_exists($path . DS . $tool . DS . $toolFile)) {
				require_once($path . DS . $tool . DS . $toolFile);
			}
			else {
				continue;
			}

			if(class_exists($toolClass)) {
				new $toolClass($api, $dom, $template);
			}
			else {
				continue;
			}
		}
	}
}

/**
 * Adds required metadata according to current session, such as currently 
 * selected language by user.
 * @param Dom $dom The current active Dom.
 */
public function addMetaData($dom) {
	if(isset($_SESSION["PhpGt_Lang"])) {
		$dom["head"]->prepend("meta", [
			"HTTP-EQUIV" => "Content-Language",
			"Content" => $_SESSION["PhpGt_Lang"]["Code"]
		]);
	}
}

/**
 * Returns the current contents of the output buffer.
 * @return string The output buffer.
 */
public function getBuffer() {
	return $this->_buffer;
}

/**
 * Flushes the buffer to the browser, and leaves the buffer clean.
 */
public function flush($clean = false) {
	echo $this->_buffer;
	if($clean) {
		$this->_buffer = "";
	}
}

/**
* Simply takes what is already in the buffer and stores it to a private
* variable. Buffer will be parsed with DOM and later flushed to the browser.
*/
private function storeBuffer() {
	$this->_buffer = ob_get_clean();
}

/**
* Attempts to load the current requested PageView file, or an arbitary
* non-required addition to the PAgeView, such as a header or footer file.
* Arbitary files are prefixed with an underscore automatically.
* @param string $fileName The file to load.
* @return int|bool On success, the filemtime of the view file is returned. If
* the file cannot be found, false is returned.
*/
private function bufferPageView($fileName = null) {
	$fileArray = null;

	if(is_null($fileName)) {
		// Requested file is stored in the FILE constant.

		// Request path is absolute, only one array element needed, with
		// direct reference to DIR and FILE.
		$fileArray = array(
			APPROOT . DS . "PageView" . DS . DIR . DS . FILE . ".html"
		);

		if(DIR === BASEDIR) {
			$fileArray[] =
				APPROOT . DS . "PageView" . DS . BASEDIR . DS . FILE . ".html";
		}

		// Ensure there is only ever one URI that can be used to access a
		// particular page by forwarding requests to the Index.html within a
		// directory.
		if(FILE === "Index"
		&& substr($_SERVER["REQUEST_URI"],
		strrpos($_SERVER["REQUEST_URI"], "/") + 1) !== "Index." . EXT) {
			$pathInfo = pathinfo($_SERVER["REQUEST_URI"]);
			$fwd = "/";
			$fwd .= $pathInfo["dirname"];

			$fwd .= "/" . $pathInfo["basename"];
			$fwd .= "/" . FILE . "." . EXT;

			$fwd = preg_replace("/\/+/", "/", $fwd);

			// Only perform the redirect if the Index.html file exists.
			$pageViewFile = APPROOT . DS . "PageView" . $fwd;
			if(file_exists($pageViewFile)) {
				http_response_code(301);
				header("Location: $fwd");
				exit;
			}
		}
	}
	else {
		// Strip any underscores, as these are added automatically.
		$fileName = trim($fileName, "_");
		$fileName = ucfirst($fileName);

		// List of PageView locations in priority order.
		$fileArray = array(
			APPROOT . DS . "PageView" 
				. DS . DIR . DS . "_{$fileName}.html",
			APPROOT . DS . "PageView" 
				. DS . FILE . DS . "_{$fileName}.html",
			APPROOT . DS . "PageView" 
				. DS . BASEDIR . DS . "_{$fileName}.html",
			APPROOT . DS . "PageView" 
				. DS . "_{$fileName}.html"
		);
	}

	// Search for the files, in priority order.
	foreach($fileArray as $file) {
		if(file_exists($file)) {
			// Once found, require the file and stop searching for others.
			// File being required is straight HTML - will be inserted into
			// the output buffer.
			require($file);
			return filemtime($file);
		}
	}

	if(is_null($fileName)) {
		// At this point, there is no PageView file loaded.
		// Must look for a dynamic file.
		// DOC: Dynamic PageView files.
		if(false !== ($dynamicFileName = $this->findDynamicPageView()) ) {
			// File being required is straight HTML - will be inserted into 
			// the output buffer.
			require($dynamicFileName);
			return filemtime($dynamicFileName);
		}
	}

	return false;
}

/**
* Attempts to find the path of a PageView's dynamic file from the current
* request. A dynamic file is named "_Dynamic.html", and the presence of
* this file in a directory means that a PageView doesn't have to exist - 
* a common dynamic file can be loaded instead, which can be manupulated by
* the page code to act as a unique PageView.
*/
private function findDynamicPageView() {
	$found = false;
	$lookPath = DIR . DS . FILE;
	while($found === false) {
		// Find position of last slash in requested page.
		$lastSlash = strrpos($lookPath, DS);
		$dynamicFile = APPROOT . DS . "PageView" . DS 
		. $lookPath . DS . "_Dynamic.html";

		// If found, stop looking.
		if(file_exists($dynamicFile)) {
			$found = $dynamicFile;
			break;
		}

		// Move up one directory closer to APPROOT and continue looking.
		$lookPath = substr($lookPath, 0, $lastSlash);

		// Cancel search when root found.
		if($lastSlash === false) {
			break;
		}
	}
	return $found;
}

}?>