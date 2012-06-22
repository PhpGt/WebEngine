<?php
class HttpError extends Exception {
	private $_errorCodeMessage = array(
		301 => "Moved Permanently",
		400 => "Bad Request",
		401 => "Unauthorized",
		403 => "Forbidden",
		404 => "Not Found",
		408 => "Request Timeout",
		410 => "Gone",
		429 => "Too Many Requests",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		503 => "Service Unavailable"
	);


	public function __construct(
	$code, $data = null, Exception $previous = null) {
		// On 404 errors, check case recursively.
		if($code === 404) { 
			// If correct cased URL found, checkCase will throw 301 error,
			// script will end here.
			$this->checkCase();
		}

		if(array_key_exists($code, $this->_errorCodeMessage)) {
			$message = $this->_errorCodeMessage[$code];
		}
		
		$this->sendHeaders($code, $message);
		if(is_string($data)) {
			$this->displayError($code, $data);
		}
		else if(is_array($data)) {
			$this->sendHeaders($data);
		}
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

	private function displayError($code, $description = "") {
		$fileName = $code . ".html";
		$pathArray = array(
			APPROOT . DS . "PageView" . DS . DIR . DS,
			APPROOT . DS . "PageView" . DS,
			GTROOT . DS . "Framework" . DS . "Error" . DS 
		);

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
						die("FATAL ERROR: Failed to load errorpage."
							. "655034494:53");
					}
					$codeNode = $dom->getElementById("errorCode");
					$msgNode = $dom->getElementById("errorMessage");
					$tsNode = $dom->getElementById("timestamp");
					$ipNode = $dom->getElementById("ipAddress");
					if(!is_null($codeNode)) {
						$codeNode->nodeValue = $code;
					}
					if(!is_null($msgNode)) {
						$msgNode->nodeValue = $code;
					}
					if(!is_null($tsNode)) {
						$tsNode->nodeValue = time();
					}
					if(!is_null($ipNode)) {
						$ipNode->nodeValue = $_SERVER["REMOTE_ADDR"];
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

	private function checkCase() {
		$dirList = explode("/", DIR);
		$path = APPROOT . DS . "PageView";
		$originalPath = $path;

		foreach($dirList as $dir) {
			if(empty($dir)) {
				continue;
			}

			$dh = opendir($path);
			while(false !== ($name = readdir($dh)) ) {
				if(strtolower($dir) == strtolower($name)) {
					$dir = $name;
					$originalDir = $name;
				}
			}
			closedir($dh);

			$path .= DS . $dir;
			$originalPath .= DS . $originalDir;
		}

		$fileName = FILE . "." . EXT;

		// Replace directory separator with forward slash for URL.
		$url = str_replace(DS, "/", $path);
		// Remove APPROOT/PageView/ from beginning of path.
		$url = substr(
			$url, 
			stripos($url, "PageView") + strlen("PageView")
		);
		// Add hostname to beginning of path.
		$url = "http"
			. (empty($_SERVER["HTTPS"]) ? "" : "s")
			. "://"
			. $_SERVER["HTTP_HOST"]
			. $url . "/";

		if($path !== $originalPath) {
			// Add fileName to the path
			$url .= $fileName;

			// Redirect to new path.
			throw new HttpError(301, array("Location" => $url));
		}

		// At this point, the directory path is either the correct case, or
		// there isn't a correct alternative to the one supplied.
		$dh = opendir($path);
		while(false !== ($name = readdir($dh)) ) {
			if(strtolower($name) == strtolower($fileName)) {
				// Add fileName to the path
				$url .= $name;
				// Redirect to new path.
				throw new HttpError(301, array("Location" => $url));
			}
		}
		closedir($path);
	}
}
?>