<?php
require_once(dirname(__FILE__) . DS . "FacebookObject.class.php");

class Facebook_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {}

	public function get($id, $authToken = null) {
		return new FacebookObject($id, $authToken);
	}

	public function like($domElement) {
		$iframe = $this->_dom->create("iframe");
		$iframe->setAttribute("src",
			"https://www.facebook.com/plugins/like.php?href=" . URL);
		$iframe->setAttribute("scrolling", "no");
		$iframe->setAttribute("frameborder", "0");
		$iframe->setAttribute("style",
			"border: none; width: 450px; height: 80px;");

		$domElement->replace($iframe);
	}
}
?>