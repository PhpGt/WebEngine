<?php
require_once(dirname(__FILE__) . DS . "PayPalGateway.class.php");

class PayPalObject {
	protected $gateway;
	protected $endpoint = '/nvp';
	
	public function __construct(PaypalGateway $gateway) {
		$this->gateway = $gateway;
	}
	
	protected function response($data){
		$request = new HttpRequest_Utility(
			$this->gateway->getHost(),
			$this->endpoint,
			'POST',
			true
		);

		$result = $request->connect($data);
		if ($result < 400) {
			return $request;
		}

		return false;
	}
	
	protected function responseParse($resp) {
		$kvpArray = explode("&", $resp);
		$result = array();

		foreach ($kvpArray as $kvp) {
			$k = strpos($kvp, '=');

			if ($k) {
				$key = trim(substr($kvp, 0, $k));
				$value = trim(substr($kvp, $k+1));

				if (!$key) {
					continue;
				}

				$result[$key] = urldecode($value);
			} 
			else {
				$result[] = $kvp;
			}
		}

		return $result;
	}
	
	protected function buildQuery($data = array()) {
		$data['USER']		= $this->gateway->apiUsername;
		$data['PWD']		= $this->gateway->apiPassword;
		$data['SIGNATURE']	= $this->gateway->apiSignature;
		$data['VERSION']	= '65.0';
		$data['RETURNURL']	= $this->gateway->returnUrl;
		$data['CANCELURL']	= $this->gateway->cancelUrl;
		
		$query				= http_build_query($data);
		return $query;
	}
	
	
	protected function runQueryWithParams($data) {
		$query = $this->buildQuery($data);
		$reponse = $this->response($query);
		if (!$response) {
			return false;
		}
		
		$content = $response->getContent();
		$result = $this->responseParse($contnet);
		$result['ACK'] = strtoupper($result['ACK']);
		return $result;
	}
}
?>