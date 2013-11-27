<?php class Http {
/**
 * DEPRECATED: Use PHP's internal HttpRequest object instead.
 * http://www.php.net/manual/en/class.httprequest.php
 * 
 * An object-oriented wrapper to the cURL module. Can be constructed with a
 * single or multiple URLs, to be executed using curl_multi.
 */
private $_urlArray = array();
private $_ch = array();
private $_chm = null;
private $_headers = array();

public $response = null;

public function __construct($url = null, $method = "GET", $parameters = null) {
	require_once(__DIR__ . "/Http_Exception.class.php");
	require_once(__DIR__ . "/Http_Response.class.php");

	$urlArray = array();
	$this->_chm = curl_multi_init();
	$this->response = new Http_Response();

	if(empty($url)) {
		$this->_ch[] = curl_init();
	}
	else {
		if(is_array($url)) {
			$urlArray = $url;
		}
		else {
			$urlArray = array($url);
		}
		foreach ($urlArray as $i => $url) {
			$this->_ch[] = curl_init();			
		}

		$this->_urlArray = $urlArray;
		$this->execute($urlArray, $method, $parameters);
	}

	$this->_headers[] = 'Expect:';
}

public function __destruct() {
	curl_multi_close($this->_chm);
	return true;
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

	foreach ($this->_ch as $ch) {
		curl_setopt($ch, $optionInt, $value);
	}
	return true;
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

	$this->_headers = array_merge($this->_headers, $headerArray);
		
	return $this->_headers;
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
public function execute($urlArray = null, $method = "GET", $parameters = null) {
	$method = strtoupper($method);
	$paramChar = "?";

	if(is_null($urlArray)) {
		$urlArray = $this->_urlArray;
	}
	if(!is_array($urlArray)) {
		$urlArray = array($urlArray);
	}

	foreach ($urlArray as $i => $url) {
		if($method === "GET"
		|| $method === "DELETE") {
			if(strstr($url, "?")) {
				if(!empty($parameters)) {
					$paramChar = "&";
				}
			}
		}
		if(!empty($parameters)) {
			foreach ($parameters as $key => $value) {
				$url .= $paramChar;
				$url .= urlencode($key) . "=" . urlencode($value);
				$paramChar = "&";
			}
		}

		curl_setopt($this->_ch[$i], CURLOPT_URL, $url);
	}

	$this->setOption("customRequest", $method);

	if($method === "POST"
	|| $method === "PUT") {
		$this->setOption("PostFields", $parameters);
	}

	foreach ($this->_ch as $ch) {
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
		curl_multi_add_handle($this->_chm, $ch);
	}

	$active = null;

	do {
		$status = curl_multi_exec($this->_chm, $active);
	} while($status == CURLM_CALL_MULTI_PERFORM || $active);

	$responseData = null;

	foreach ($this->_ch as $ch) {
		$response = curl_multi_getcontent($ch);

		$header = "";
		$body = "";

		// Surpress warning if there is no body index.
		@list($header, $body) = explode("\r\n\r\n", $response, 2);
		$headerLines = explode("\n", $header);
		$headers = array();
		foreach ($headerLines as $h) {
			$hArray = explode(":", $h);
			if(count($hArray) < 2) {
				continue;
			}
			$headers[$hArray[0]] = $hArray[1];
		}
		
		$responseData = [
			"header" => $header,
			"headers" => $headers,
			"body" => $body,
		];
		$this->response->add($responseData);
		
		curl_multi_remove_handle($this->_chm, $ch);
	}
	return $this->response;
}

}#