<?php
final class Request {
	private $_api = null;
	private $_pageCode = null;

	public function __construct($settings) {
		$contentType = "text/html";
		if(EXT == "json") { 
			$contentType = "application/json";
			// TODO: Output json from requested API, or fail if invalid request.
			echo("TODO: API Creation, computation.");
		}
		else {
			$pageCodeFile  = APPROOT . DS . "PageCode" . DS . FILEPATH . ".php";
			$pageCodeClass = FILECLASS . "_PageCode";
			if(file_exists($pageCodeFile)) {
				require($pageCodeFile);
				if(class_exists($pageCodeClass)) {
					$this->_pageCode = new $pageCodeClass();
				}
			}
		}

		// Check whether whole request is cached.
		if($settings["App"]->isCached()) {
			// TODO: Cache output.
		}

		header("Content-Type: $contentType; charset=utf-8");
		header("X-Powered-By: PHP.Gt Version " . VER);

		// Check for framework-reserved requests.
		if(in_array(strtolower(FILE), $settings["App"]->getReserved())
		|| in_array(strtolower(BASEDIR), $settings["App"]->getReserved() )) {

			// Request is reserved, pass request on to the desired function.
			$reservedName = BASEDIR == ""
			? FILE
			: BASEDIR;
			$reservedFile = GTROOT . DS . "Framework" . DS 
			. "Reserved" . DS . ucfirst($reservedName) . ".php";
			if(file_exists($reservedFile)) {
				require($reservedFile);
				$reservedClassName = $reservedName . "_Reserved";
				if(class_exists($reservedClassName)) {
					new $reservedClassName();
				}
				exit;
			}
			die("Reserved");
		}
		session_start();
	}

	public function getPageCode() {
		return $this->_pageCode;
	}
}
?>