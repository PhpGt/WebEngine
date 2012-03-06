<?php
/**
 * TODO: Docs.
 * Google Analytics.
 */
class Analytics_PageTool extends PageTool {
	public function go($api, $dom, $template, $tool) {
	}

	public function track($trackingCode) {
		$js = file_get_contents(dirname(__FILE__) . DS . "Analytics.tool.js");
		if($js === false) {
			throw new HttpError(500, "Google Analytics script failure");
		}
		$js = str_replace("{ANALYTICS_CODE}", $trackingCode, $js);

		//$script = $this->_dom->create("script", null, $js);
		$this->_dom["head"]->append("script", null, $js);
	}
}
?>