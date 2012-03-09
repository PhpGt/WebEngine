<?php
require_once(dirname(__FILE__) . DS . "FacebookObject.class.php");

class Facebook_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {}

	public function get($id, $authToken = null) {
		return new FacebookObject($id, $authToken);
	}
}
?>