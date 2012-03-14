<?php
class PaypalGateway {
	public $apiUsername;
	public $apiPassword;
	public $apiSignature;
	public $testMode;
	public $returnUrl;
	public $cancelUrl;
	
	public function __construct(
	$apiUsername = "",
	$apiPassword = "",
	$apiSignature = "",
	$testMode = false) {
		
		$this->apiUsername	= $apiUsername;
		$this->apiPassword	= $apiPassword;
		$this->apiSignature	= $apiSignature;
		$this->testMode		= $testMode;
	}
	
	public function getHost() {
		return $this->testMode
			? "api-3t.sandbox.paypal.com"
			: "api-3t.paypal.com";
	}
	
	public function getGate() {
		return $this->testMode
			? "https://www.sandbox.paypal.com/cgi-bin/webscr?"
			: "https://www.paypal.com/cgi-bin/webscr?";
	}
	
}
?>