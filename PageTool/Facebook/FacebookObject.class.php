<?php
class FacebookObject {
	private $_id;
	private $_authToken;

	public function __construct($id, $authToken) {
		$this->_id = $id;
		$this->_authToken = $authToken;
	}

	public function getType() {
		return null;
	}
}
?>