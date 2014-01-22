<?php class PayPal_PageTool extends PageTool {

private $_apiVer = "v1";
private $_sessionNS = "PhpGt.Tool.PayPal";
private $_sessionToken = "PhpGt.Tool.PayPal.token";
private $_sessionTokenExpiry = "PhpGt.Tool.PayPal.tokenExpiry";
private $_host = null;

public function go($api, $dom, $template, $tool) {}

/**
 * Gets an access token and stores it to the Session.
 */
public function init($clientID, $secret, $sandbox = false) {
	$this->_host = $sandbox
		? "https://api.sandbox.paypal.com/" . $this->_apiVer . "/"
		: "https://api.paypal.com/" . $this->_apiVer . "/";

	$token = null;

	if(Session::exists($this->_sessionToken)
	&& Session::get($this->_sessionTokenExpiry) > time()) {
		$token = Session::get($this->_sessionToken)
	}
	else {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_host . "oauth2/token");
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Accept: application/json",
			"Accept-Language: en_GB",
		]);
		curl_setopt($ch, CURLOPT_USERPWD, "$clientID:$secret");
		curl_setopt($ch, CURLOPT_POSTFIELDS, [
			"grant_type" => "client_credentials",
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$curl_result = curl_exec($ch);
		curl_close($ch);

		$obj = json_decode($curl_result);
		if(isset($obj->access_token)
		&& isset($obj->expires_in)
		&& isset($obj->token_type)
		&& $obj->token_type == "Bearer") {
			Session::set($this->_sessionToken, $obj->access_token);
			Session::set($this->_sessionTokenExpiry, time() + $obj->expires_in);
		}
		else {
			Session::delete($this->_sessionNS);
			return false;
		}
	}

	return $token;
}

private check() {
	if(!Session::exists($this->_sessionNS)) {
		throw new Exception("PayPal Auth token is not initialised");
	}

	if(time() > Session::get($this->_sessionTokenExpiry)) {
		throw new Exception("PayPal token is expired");
	}
}

}#