<?php class Http {
/**
 * An object-oriented wrapper to the cURL module.
 */
private $_ch;

public $response = array();

public function __construct() {
	require_once(__DIR__ . "/Http_Exception.class.php");
	$this->_ch = curl_init();
}

public function __destruct() {
	curl_close($this->_ch);
}

/**
 * Wrapper for any missed cURL functionality.
 * @param string $option The CURLOPT_* to set. For CURLOPT_COOKIE, simply pass 
 * the string "cookie".
 * @param mixed $value What to set the CURLOPT to.
 * @return True on success, false on failure.
 */
public function setOption($option, $value) {
	$optionInt = null;
	if(is_string($option)) {
		$optionInt = constant("CURLOPT_" . strtoupper($option));
	}
	else if(is_int($option)) {
		$optionInt = $option;
	}

	if(is_null($optionInt)) {
		throw new Http_Exception("Invalid option passed to cURL.");
	}
	return curl_setopt($this->_ch, $optionInt, $value);
}

public function setHeader($header) {
	$headerArray = array();

	if(is_string($header)) {
		$headerArray[] = $header;
	}
	else if(is_array($header)) {
		$headerArray = $header;
	}
	else {
		throw new Http_Exception("Http setHeader() only accepts string/array.");
	}

	curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $headerArray);
	return $headerArray;
}

/**
 * Executes the cURL request with a given method and optional parameters.
 * GET and DELETE methods cannot accept parameters in the body, but the URL
 * querystring can be built up by passing key value pairs as an array.
 * An exception will be thrown if the URL contains a querystring, the method is
 * GET or DELETE, and parameters are passed.
 *
 * @param string $url The URL to request
 * @param string $method Optional. The HTTP method to use.
 * @param array $parameters Optional. The form data to use in a POST or PUT.
 * Could also be used to build the query string on GET or DELETE requests.
 * @return string The HTTP response. Use responseCode property to obtain the
 * latest response code.
 */
public function execute($url, $method = "GET", $parameters = array()) {
	$method = strtoupper($method);

	if($method === "GET"
	|| $method === "DELETE") {
		if(strstr($url, "?")) {
			if(!empty($parameters)) {
				throw new Http_Exception(
					"Only POST or PUT methods can be passed parameters.");
			}
		}
		else {
			if(!empty($parameters)) {
				$paramChar = "?";
				foreach ($parameters as $key => $value) {
					$url .= $paramChar;
					$url .= urlencode($key) . "=" . urlencode($value);
					$paramChar = "&";
				}
			}
		}
	}
	else {
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $parameters);
	}

	curl_setopt($this->_ch, CURLOPT_URL, $url);
	curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
	curl_setopt($this->_ch, CURLOPT_HEADER, true);

	$response = curl_exec($this->_ch);
	$curlInfo = curl_getinfo($this->_ch);

	$this->response = array();
	// Converts $curlInfo to PHP.Gt naming conventions.
	foreach ($curlInfo as $key => $value) {
		$spaces = str_replace("_", " ", $key);
		$ccKey = ucwords($spaces);
		$ccKey = str_replace(" ", "", $ccKey);

		$this->response[$ccKey] = $value;
	}

	list($header, $body) = explode("\r\n\r\n", $response, 2);
	$headerLines = explode("\n", $header);
	$headers = array();
	foreach ($headerLines as $h) {
		$colonPos = strpos($h, ":");
		if($colonPos === false) {
			continue;
		}
		$key = substr($h, 0, $colonPos);
		$value = substr($h, $colonPos + 1);
		$headers[$key] = $value;
	}

	$this->response["Header"] = $header;
	$this->response["Headers"] = $headers;
	$this->response["Body"] = $body;

	return true;
}

}?>