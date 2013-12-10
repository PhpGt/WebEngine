<?php class HttpError extends Exception {
/**
 * TODO: Docs.
 */
private $_errorCodeMessage = array(
	301 => "Moved Permanently",		// Permanent redirect.
	302 => "Found",					// Temporary redirect.
	400 => "Bad Request",			// Malformed/illegal URL.
	401 => "Unauthorized",			// Requires authorisation to view.
	403 => "Forbidden",				// Not accessible (from IP/hostname).
	404 => "Not Found",
	408 => "Request Timeout",		
	410 => "Gone",					// Deleted a page.
	429 => "Too Many Requests",		// Use to limit API calls, etc.
	500 => "Internal Server Error",	// Uncaught errors.
	501 => "Not Implemented",
	503 => "Service Unavailable"
);

private $_errorLogLevels = array(
	"TRACE" => [301, 302, ],
	"INFO" => [400, 401, 403, 404, ],
	"WARN" => [408, 410, ],
	"ERROR" => [429, ],
	"FATAL" => [500, 501, 503, ],
);


public function __construct(
$code, $data = null, Exception $previous = null) {
	// On 404 errors, check case recursively.
	if($code === 404) {
		// If correct cased URL found, checkCase will throw 301 error,
		// script will end here.
		$this->checkCase();
		$this->checkDirFile();
	}

	if(is_null($data)
	|| empty($_SESSION["PhpGt_Development"])) {
		if(array_key_exists($code, $this->_errorCodeMessage)) {
			$data = $this->_errorCodeMessage[$code];
		}		
	}
	
	http_response_code($code);
	$this->displayError($code, $data);
	exit;
}

/**
 * Sends raw HTTP headers using HTTP/1.1 specification or kvp specification.
 * @param int|array $header Either the numerical HTTP/1.1 code, or an array
 * containing key-value-pairs to send.
 * @param string $value Optional. The associated string to send with a
 * HTTP/1.1 code. Not required if kvp sent in first argument.
 */
private function sendHeaders() {
	$args = func_get_args();
	if(is_int($args[0]) && is_string($args[1])) {
		header($_SERVER["SERVER_PROTOCOL"]
			. " "
			. $args[0] 
			. " " 
			. $args[1]);
	}
	else if(is_array($args[0])) {
		foreach ($args[0] as $key => $value) {
			header($key . ": " . $value);
		}
	}
}

private function displayError($code, $data = array("")) {
	$fileName = $code . ".html";
	$pathArray = array(
		APPROOT . "/PageView/" . DIR . "/",
		APPROOT . "/PageView/",
		GTROOT  . "/Framework/Error/",
	);

	$message = "";
	if(isset($data["Message"])) {
		$message = $data["Message"];
	}
	if(isset($data["Number"])) {
		$message .= "\nError number: {$data["Number"]}";
	}
	if(isset($data["File"])) {
		$message .= "\nFile: {$data["File"]}";
	}
	if(isset($data["Line"])) {
		$message .= "\nLine: {$data["Line"]}";
	}
	if(isset($data["Context"])) {
		$message .= "\n";
		foreach($data["Context"] as $contextLine) {
			$message .= "\n$contextLine";
		}
	}


	if(count($data) == 1) {
		$message = $data[0];
	}
	if(is_string($data)) {
		$message = $data;
	}

	$logLevel = "INFO";
	$logger = Log::get();
	foreach ($this->_errorLogLevels as $key => $value) {
		if(in_array($code, $value)) {
			$logLevel = $key;	
		}
	}
	$logger->$logLevel($_SERVER["REQUEST_URI"] . " - " . $message);

	foreach ($pathArray as $path) {
		if(is_dir($path)) {
			if(file_exists($path . $fileName)) {
				ob_clean();
				require $path . $fileName;
				break;
			}
			if(file_exists($path . "_Error.html")) {
				$html = file_get_contents($path . "_Error.html");
				$dom = new DomDocument("1.0", "utf-8");
				libxml_use_internal_errors(true);
				if(!$dom->loadHTML($html)) {
					die("FATAL ERROR: Failed to load errorpage.");
				}
				$codeNode = $dom->getElementById("errorCode");
				$msgNode = $dom->getElementById("errorMessage");
				$tsNode = $dom->getElementById("timestamp");
				$ipNode = $dom->getElementById("ipAddress");
				$traceNode = $dom->getElementById("trace");
				$headNode = $dom->getElementsByTagName("head")->item(0);
				$titleNode = $headNode->getElementsByTagName("title");
				if($titleNode->length > 0) {
					if(strlen($titleNode->item(0)->nodeValue) === 0) {
						$titleNode->item(0)->nodeValue = "Error " . $code;
					}
				}
				if(!is_null($codeNode)) {
					$codeNode->nodeValue = $code;
				}
				if(!is_null($msgNode)) {
					$msgNode->nodeValue = $message;
				}
				if(!is_null($tsNode)) {
					$tsNode->nodeValue = time();
				}
				if(!is_null($ipNode)) {
					$ipNode->nodeValue = $_SERVER["REMOTE_ADDR"];
				}
				if(!is_null($traceNode)) {
					if(!empty($_SESSION["PhpGt_Development"])) {
						if(function_exists("xdebug_get_function_stack")) {
							$stack = array_reverse(xdebug_get_function_stack());
							foreach ($stack as $stackI) {
								$pre = $dom->createElement("pre");
								$m = "";
								if(isset($stackI["class"])) {
									$m .= "Class: " . $stackI["class"] . "\n";
								}
								if(isset($stackI["function"])) {
									$m .= "Function: "
										. $stackI["function"] . "\n";
								}
								$m .= "Line: " . $stackI["line"] . "\n";
								$m .= "File: " . $stackI["file"] . "\n\n";
								
								$pre->nodeValue = $m;
								$traceNode->appendChild($pre);
							}
						}
					}
				}

				ob_clean();
				$dom->formatOutput = true;
				echo $dom->saveHTML();
				ob_flush();

				break;
			}
		}
	}
}

/**
 * If a directory is requested that doesn't exist, before throwing a 404 PHP.Gt
 * checks to see if there is actually an html file of that name.
 * @return bool False if there is no file found. Will never return true, as the
 * script ends early, sending 301 headers.
 */
private function checkDirFile() {
	
}

/**
 * Checks each directory and the current requested file in the URI against the
 * actual directory structure within the PageView directory.
 * Each entry is compared, and any inconsistencies in case are fixed, and then
 * 301 forwarded to the correct URI. 
 * @return bool True if case is correct. Will never return false, as the script
 * ends early, sending 301 headers.
 */
private function checkCase() {
	$pvPath = APPROOT . "/PageView/";
	// Obtain array of each directory name.
	$dirList = explode("/", DIR);
	$origDirList = $dirList;

	$cwd = $pvPath;

	$file = FILE;
	$origFile = $file;

	// Recursively move down the directories, looking for incorrect case.
	foreach($dirList as $key => &$dir) {
		if(is_dir($cwd)) {
			// Find the actual path of the dir, compare cases.
			$dh = opendir($cwd);
			while(false !== ($entry = readdir($dh)) ) {
				if(strtolower($entry) === strtolower($dir)) {
					if($entry !== $dir) {
						$dir = $entry;
						break;
					}
				}
			}
			closedir($dh);
		}

		$cwd .=  $dir . "/";
	}

	// At this point, $dirList holds a correctly-cased array of directories.
	// Now the file may be fixed:
	if(!is_dir($cwd)) {
		return false;
	}
	$dh = opendir($cwd);
	while(false !== ($entry = readdir($dh)) ) {
		$fn = substr($entry, 0, strrpos($entry, "."));
		if(strtolower($fn) === strtolower($file)) {
			$file = $fn;
			break;
		}
	}
	closedir($dh);

	// Compare the original directory array and file with the new.
	// If different, forward them.
	$diff = array_diff($dirList, $origDirList);
	if(!empty($diff)
	|| $file !== $origFile) {
		$fwd = "/" . implode("/", $dirList);
		$fwd .= "/" . $file;
		$fwd .= "." . EXT;

		$fwd = preg_replace("/\/+/", "/", $fwd);

		http_response_code(301);
		header("Location: $fwd");
		exit;
	}

	// At this point, there are no case fixes necessary.
	return true;
}

}#