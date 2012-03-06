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
			}
		}

		// TODO: Do something with $description.
	}
}
?>