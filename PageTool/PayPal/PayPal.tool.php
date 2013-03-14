<?php class PayPal_PageTool extends PageTool {
private $_apiUsername;
private $_apiPassword;
private $_apiSignature;
private $_sandbox = false;

private $_gateway;

/**
 * This function requires extra parameters!
 * Username, password, API signature, [Optional] Sandbox.
 */
public function go($api, $dom, $template, $tool) {
	require_once(dirname(__FILE__) . "/PayPal.class.php");
	require_once(dirname(__FILE__) . "/PayPal_ExpressCheckout.class.php");
	
	$this->_apiUsername  = func_get_arg(4);
	$this->_apiPassword  = func_get_arg(5);
	$this->_apiSignature = func_get_arg(6);

	if(func_num_args() > 7) {
		$this->_sandbox = func_get_arg(7) == "Sandbox";
	}

	$this->_gateway = $this->getGateway();

	// Catch any POSTed data from the buttons that are created using the
	// functions below.
}

private function getGateway() {
	$this->_gateway = new PayPalGateway();
	$this->_gateway->apiUsername  = $this->_apiUsername;
	$this->_gateway->apiPassword  = $this->_apiPassword;
	$this->_gateway->apiSignature = $this->_apiSignature;
	$this->_gateway->testMode = $this->_sandbox;
}

public function setPages($successUrl, $cancelUrl) {
	$this->_gateway->returnUrl = $successUrl;
	$this->_gateway->cancelUrl = $cancelUrl;
}

/**
 * Transforms a regular button or input into a PayPal buy now button.
 * If there is no form surrounding the button, a form will automatically
 * be created.
 */
public function buyNow($domButton, $itemDetails = array()) {
	var_dump(xdebug_get_function_stack());
	die("HERE!!!");
}

}?>