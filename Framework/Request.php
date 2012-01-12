<?php
final class Request {
	public $api = null;
	public $pageCode = null;
	public $pageCodeCommon = null;
	public $contentType;

	public function __construct($config) {
		$this->contentType = "text/html";
		session_start();

		if(EXT == "json") { 
			$this->contentType = "application/json";
			
			// Output json from requested API, or fail if invalid request.
			$errorObject = new StdClass();
			$errorObject->error = "API module not found.";

			// Look for requested API. Note that API requests have to always
			// be in the root directory i.e. /Blog.json, and can never be nested
			// i.e. /Blog/2010/01/Blog.json
			$apiName   = ucfirst(FILE);
			$className = $apiName . "_Api";
			$fileName  = $apiName . ".api.php";
			$apiPathArray = array(
				APPROOT . DS . "Api" . DS,
				GTROOT  . DS . "Api" . DS
			);
			foreach ($apiPathArray as $path) {
				if(file_exists($path . $fileName)) {
					require_once($path . $fileName);
					break;
				}
			}
			if(class_exists($className)) {
				$this->api = new $className();
				$data = $_GET;
				if(isset($data["url"])) {
					unset($data["url"]);
				}
				if(isset($data["ext"])) {
					unset($data["ext"]);
				}
				if(!isset($data["Method"])) {
					$this->api->setError("API method not specified.");
					return;
				}

				$methodName = $data["Method"];
				unset($data["Method"]);
				$this->api->setMethodName($methodName);
				$this->api->setApiName($apiName);

				$paramArray = array();
				foreach ($data as $key => $value) {
					$paramArray[$key] = $value;
				}
				$this->api->setMethodParams($paramArray);

				if(!method_exists($this->api, lcfirst($methodName)) &&
				!in_array(ucfirst($methodName), $this->api->externalMethods)) {
					$this->api->setError("API method does not exist.");
					return;
				}
			}
			else {
				$this->api = $errorObject;
				return;
			}
		}
		else {
			// Look for PageCode that's relative to the requested path.
			$pageCodeFile  = APPROOT . DS . "PageCode" . DS . FILEPATH . ".php";
			$pageCodeClass = FILECLASS . "_PageCode";
			if(file_exists($pageCodeFile)) {
				require($pageCodeFile);
				if(class_exists($pageCodeClass)) {
					$this->pageCode = new $pageCodeClass();
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
					$this->pageCodeCommon = new $pageCodeComClass();
				}
			}
		}

		// Check whether whole request is cached.
		if($config["App"]->isCached()) {
			// TODO: Cache output.
		}

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

		return;
	}
}
?>