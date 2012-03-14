<?php
require_once(dirname(__FILE__) . DS . "PayPal.class.php");
require_once(dirname(__FILE__) . DS . "PayPal_ExpressCheckout.class.php");

class PayPal_PageTool extends PageTool {
	private $_sandbox = false;
	public function go($api, $dom, $template, $tool) {
		if(func_num_args() > 7) {
			$this->_sandbox = func_get_arg(7) == "Sandbox";
		}

		// Perform authentication to obtain API key here.

		// Catch any POSTed data from the buttons that are created using the
		// functions below.
	}

	public function getGateway($apiUsername, $apiPassword, $apiSignature,
	$returnUrl = "/PayPalComplete.html", $cancelUrl = "/PayPalCancel.html") {
		$gateway = new PayPalGateway();
		$gateway->apiUsername = "";
		$gateway->apiPassword = "";
		$gateway->apiSignature= "";
		$gateway->testMode = $this->_sandbox;

		$gateway->returnUrl = $returnUrl;
		$gateway->cancelUrl = $cancelUrl;
	}

	/**
	 * Transforms a regular button or input into a PayPal buy now button.
	 * If there is no form surrounding the button, a form will automatically
	 * be created.
	 */
	public function buyNow(DomEl $button, $itemDetails = array()) {

	}
}
?>