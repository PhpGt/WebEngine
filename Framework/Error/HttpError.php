<?php
class HttpError extends Exception {
	public function __construct(
	$message, $code = 0, Exception $previous = null) {
		$this->sendHeaders($message, $code);
		$this->displayError($code);
		exit;
	}

	private function sendHeaders($message, $code) {
		header($_SERVER["SERVER_PROTOCOL"] . " " . $code . " " . $message);
	}

	private function displayError($code) {
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
	}
}
?>