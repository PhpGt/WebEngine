<?php class Vimeo_PageTool extends PageTool {

/**
 * go() starts an *advanced* API call for Vimeo. This is not required for the 
 * simple (public) API.
 */
public function go($api, $dom, $template, $tool) {
	// TODO: Implement advanced API.
}

/**
 * TODO: Docs.
 */
public function __call($name, $arguments) {
	if(empty($arguments)) {
		// TODO: Throw proper error.
		die("Vimeo error: No arguments passed.");
	}
	$userId = $arguments[0];
	if(strpos($name, "get") === 0) {
		$name = substr($name, 3);
	}
	$name = preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $name);
	$name = strtolower($name);

	$validRequests = array(
		"info",
		"videos",
		"likes",
		"appears_in",
		"all_videos",
		"subscriptions",
		"albums",
		"channels",
		"groups"
	);
	if(!in_array($name, $validRequests)) {
		// TODO: Throw proper error.
		die("Vimeo error: Invalid request: $name.");
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,
		"http://vimeo.com/api/v2/{$userId}/{$name}.php");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	curl_close($ch);

	$result = unserialize($response);
	return $result;
}

}#