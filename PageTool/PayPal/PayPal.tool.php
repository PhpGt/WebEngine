<?php class PayPal_PageTool extends PageTool {

const STATE_APPROVED = "approved";

private $_apiVer = "v1";
private $_sessionNS = "PhpGt.Tool.PayPal";
private $_sessionToken = "PhpGt.Tool.PayPal.token";
private $_sessionTokenExpiry = "PhpGt.Tool.PayPal.tokenExpiry";
private $_sessionLinks = "PhpGt.Tool.PayPal.Payment.links";
private $_sessionPaymentID = "PhpGt.Tool.PayPal.Payment.ID";
private $_clientID = null;
private $_secret = null;
private $_host = null;

public function go($api, $dom, $template, $tool) {}

/**
 * Gets an access token and stores it to the Session.
 */
public function init($clientID, $secret, $production = false) {
	// Store clientID and secret internally to allow for automatic 
	// refreshing of OAuth2 token.
	$this->_clientID = $clientID;
	$this->_secret = $secret;
	$this->_production = $production;

	// Build the correct host. When production is false, use PayPal's sandbox.
	$this->_host = $production
		? "https://api.paypal.com/" . $this->_apiVer . "/"
		: "https://api.sandbox.paypal.com/" . $this->_apiVer . "/";

	$token = null;

	// Return early with cached auth token if expiry is valid.
	if(Session::exists($this->_sessionToken)
	&& Session::get($this->_sessionTokenExpiry) > time()) {
		$token = Session::get($this->_sessionToken);
	}
	else {
		// Request a new auth token. Once successfully requested, the token is
		// saved to the session.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Accept: application/json",
			"Accept-Language: en_US",
			"Content-Type: application/x-www-form-urlencoded",
		]);
		curl_setopt($ch, CURLOPT_URL, $this->_host . "oauth2/token");
		curl_setopt($ch, CURLOPT_USERPWD, "$clientID:$secret");
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
			"grant_type" => "client_credentials",
		]));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$curl_result = curl_exec($ch);
		$curl_info = curl_getinfo($ch);

		if($curl_info["http_code"] !== 200) {
			throw new Exception("PayPal initialisation returned HTTP "
				. $curl_info["http_code"]);
		}

		curl_close($ch);

		$obj = json_decode($curl_result);

		// Check the object returned has all the required parameters.
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

/**
 * Called to initiate or complete an active payment. First step is to request a
 * new payment to be approved by user on PayPal's servers. Once the payment is
 * approved, PayPal returns the user to the application with a query string of
 * ?token={token}&PayerID={ID}.
 *
 * The second step of this function is to consume the token/PayerID parameters
 * and execute the payment, by calling executePayment method.
 *
 * @param $item array The transaction's item details. The array can be an
 * array of item arrays, or a single item array. Item array keys can
 * include: "name", "quantity", "price", "tax" (amount), "sku"
 * (stock keeping unit).
 * @param $details array The transaction's details. The array keys can include:
 * "description" (transaction's description), "shipping" (amount), 
 * "tax" (amount), "address" (if different to payment address).
 * @param $currency string Three letter currency code.
 * @param $returnUrl string The full URL to the page where the user should be
 * forwarded upon successful transaction (for processing by your application).
 * Defaults to the current URL.
 * @param $cancelUrl string The full URL to the page where the user should be
 * forwarded upon cancelling the transaction.
 */
public function pay($item, $details, $currency,
$returnUrl = null, $cancelUrl = null) {
	$logger = Log::get("PayPal");

	if(false !== ($obj = $this->executePayment()) ) {
		return $obj;
	}

	// Check the access token is fresh.
	$this->check();

	// Build up the endpoint's base URL.
	$defaultUrl = "http"
		. (empty($_SERVER["HTTPS"]) ? "" : "s")
		. "://"
		. $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

	// Build the transaction object for item(s).
	$transaction = new StdClass();

	$transaction->item_list = new StdClass();
	$transaction->item_list->items = [];
	$transaction->amount = new StdClass();
	$transaction->amount->currency = $currency;
	$transaction->amount->details = new StdClass();
	$transaction->amount->details->subtotal = 0.00;

	if(!is_array($item)) {
		throw new Exception(
			"PayPal PageTool pay() expects parameter 1 to be an item array.");
	}

	// Check if the $item parameter is an accociative/indexed array.
	if(array_keys($item) !== range(0, count($arr) - 1)) {
		// Associative array - wrap the item in an array.
		$item = [$item];
	}

	foreach ($item as $i) {
		$itemObj = new StdClass();
		$itemObj->quantity = isset($i["quantity"])
			? (string)$i["quantity"]
			: "1";
		if(!isset($i["name"])) {
			throw new Exception("Item name not supplied");
		}
		if(!isset($i["price"])) {
			throw new Exception("Item price not supplied");
		}
		$itemObj->name = $i["name"];
		$itemObj->price = (string)$i["price"];

		if(isset($i["sku"])) {
			$itemObj->sku = (string)$i["sku"];
		}

		$transaction->amount->details->subtotal += $i["price"];
		$transaction->amount->total += $i["price"];
		$transaction->item_list->items[] = $itemObj;
	}

	if(isset($details["description"])) {
		$transaction->description = $details["description"];
	}
	if(isset($details["shipping"])) {
		$transaction->amount->details->shipping = $details["shipping"];
		$transaction->amount->total += $details["shipping"];
	}
	if(isset($details["tax"])) {
		$transaction->amount->details->shipping = $details["tax"];
		$transaction->amount->total += $details["tax"];
	}


	$obj = new StdClass();
	$obj->intent = "sale";
	$obj->redirect_urls = new StdClass();
	$obj->redirect_urls->return_url = is_null($returnUrl)
		? $defaultUrl
		: $returnUrl;
	$obj->redirect_urls->cancel_url = is_null($cancelUrl)
		? $defaultUrl
		: $cancelUrl;
	$obj->payer = new StdClass();
	$obj->payer->payment_method = "paypal";
	$obj->transactions = [
		$transaction
	];

	$jsonData = json_encode($obj);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $this->_host . "payments/payment");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Content-Type: application/json",
		"Authorization: Bearer " . Session::get($this->_sessionToken),
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$curl_result = curl_exec($ch);
	curl_close($ch);

	$obj = json_decode($curl_result);
	
	if(isset($obj->state)
	&& $obj->state == "created"
	&& isset($obj->links)
	&& isset($obj->id)) {
		Session::set($this->_sessionLinks, $obj->links);
		Session::set($this->_sessionPaymentID, $obj->id);
		foreach ($obj->links as $link) {
			if($link->rel == "approval_url") {
				header("Location: " . $link->href);
				exit;
			}
		}
	}
	else {
		$logger->error($curl_result);
		throw new Exception("Payment failed to be created.");
	}
}

/**
 * Looks for token and PayerID parameters in the querystring, which is added
 * by PayPal after approving a payment's creation.
 */
public function executePayment() {
	if(!isset($_GET["token"])
	|| !isset($_GET["PayerID"])) {
		return false;
	}

	if(!Session::exists($this->_sessionPaymentID)) {
		throw new Exception(
			"PayPal PageTool attempting to execute non-existant payment.");
	}

	$ID = Session::get($this->_sessionPaymentID);

	$obj = new StdClass();
	$obj->payer_id = $_GET["PayerID"];

	$url = $this->_host . "payments/payment/$ID/execute";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		"Content-Type: application/json",
		"Authorization: Bearer " . Session::get($this->_sessionToken),
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($obj));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$curl_result = curl_exec($ch);
	curl_close($ch);

	$obj = json_decode($curl_result);

	if(isset($obj->state)
	&& $obj->state = "approved") {
		return $obj;
	}
	else {
		throw new Exception("PayPal PageTool payment execution error.");
	}
}

private function check() {
	if(!Session::exists($this->_sessionNS)) {
		throw new Exception("PayPal Auth token is not initialised.");
	}

	if(time() > Session::get($this->_sessionTokenExpiry)) {
		if(false === $this->init(
		$this->_clientID, $this->_secret, $this->_production)) {
			throw new Exception("PayPal Auth token failed to refresh.");
		}
	}

	return true;
}

}#