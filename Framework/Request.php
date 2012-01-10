<?php
final class Request {
	private $_api = null;
	private $_pageCode = null;
	private $_pageCodeCommon = null;

	public function __construct($config) {
		$contentType = "text/html";
		if(EXT == "json") { 
			$contentType = "application/json";
			// TODO: Output json from requested API, or fail if invalid request.
			echo("TODO: API Creation, computation.");
		}
		else {
			// Look for PageCode that's relative to the requested path.
			$pageCodeFile  = APPROOT . DS . "PageCode" . DS . FILEPATH . ".php";
			$pageCodeClass = FILECLASS . "_PageCode";
			if(file_exists($pageCodeFile)) {
				require($pageCodeFile);
				if(class_exists($pageCodeClass)) {
					$this->_pageCode = new $pageCodeClass();
				}
			}

			// Look for common PageCode for current directory.
			$pageCodeComFile = APPROOT . DS . "PageCode" 
				. DS . DIR . DS . "_Common.php"; 
			$pageCodeComClass = str_replace("/", "_", DIR) 
				. "__Common_PageCode";
			if(file_exists($pageCodeComFile)) {
				require($pageCodeComFile);
				if(class_exists($pageCodeComClass)) {
					$this->_pageCodeCommon = new $pageCodeComClass();
				}
			}
		}

		// Check whether whole request is cached.
		if($config["App"]->isCached()) {
			// TODO: Cache output.
		}

		header("Content-Type: $contentType; charset=utf-8");
		header("X-Powered-By: PHP.Gt Version " . VER);

		// Check for framework-reserved requests.
		if(in_array(strtolower(FILE), $config["App"]->getReserved())
		|| in_array(strtolower(BASEDIR), $config["App"]->getReserved() )) {

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

	public function getPageCodeCommon() {
		return $this->_pageCodeCommon;
	}
}
?>