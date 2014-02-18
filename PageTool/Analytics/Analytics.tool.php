<?php class Analytics_PageTool extends PageTool {
/**
 * Google Analytics.
 * This simple PageTool doesn't have any functionality in the go() method.
 * Instead, pass the tracking code into the track() method.
 */
public function go($api, $dom, $template, $tool) { }

/**
 * Injects the required JavaScript code where needed to start tracking using
 * Google Analytics.
 *
 * @param string $trackingCode Your Google Analytics account code, looks like 
 * this: UA-12345678-1
 */
public function track($trackingCode) {
	if(!$this->_dom instanceof Dom) {
		// No dom initialised... can't track.
		return;
	}
	$js = file_get_contents(dirname(__FILE__) . "/Include/Analytics.tool.js");
	if($js === false) {
		throw new HttpError(500, "Google Analytics script failure");
	}
	$js = str_replace("{ANALYTICS_CODE}", $trackingCode, $js);

	$scriptToInsertBefore = null;
	$existingScript = $this->_dom["head > script"];
	if($existingScript->length > 0) {
		$scriptToInsertBefore = $existingScript[0];
	}

	$script = $this->_dom->createElement(
		"script", 
		["data-PageTool" => "Analytics"],
		$js
	);

	$this->_dom["head"]->insertBefore($script, $scriptToInsertBefore);
}

}#