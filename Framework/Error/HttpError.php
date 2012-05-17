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
	$code, $description = null, Exception $previous = null) {
		if(array_key_exists($code, $this->_errorCodeMessage)) {
			$message = $this->_errorCodeMessage[$code];
		}
		
		$this->sendHeaders($code, $message);
		$this->displayError($code, $description);
		exit;
	}

	private function sendHeaders($code, $message) {
		header($_SERVER["SERVER_PROTOCOL"] . " " . $code . " " . $message);
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
}
?>