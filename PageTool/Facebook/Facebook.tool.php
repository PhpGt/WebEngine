<?php class Facebook_PageTool extends PageTool {
private $_sdkStarted = false;
private $_fbRootJavaScript = null;
private $_accessToken = null;

/**
 * Starts the Facebook SDK/API for the current request. Calling go for the
 * Facebook PageTool has been made to be optional - if it hasn't been
 * called by the time something else is needed, it will automatically be
 * called via the checkSdk function.
 */
public function go($api, $dom, $template, $tool) {
	if($this->_sdkStarted) {
		return;
	}

	// Pre-written JavaScript, provided by Facebook Developer website:
	$token = $this->_accessToken;
	$js = <<<JS
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=$token";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
JS;

	$this->_fbRootJavaScript = $dom->create("script", null, $js);
	$dom["body"]->prepend($this->_fbRootJavaScript);

	$this->_sdkStarted = true;
}

public function setAccessToken($token) {
	$this->_accessToken = $token;
}

public function checkSdk() {
	if($this->_sdkStarted) {
		return;
	}

	return $this->go(
		$this->_api, 
		$this->_dom, 
		$this->_template, 
		$this->_tool
	);
}

public function getGraphUrl($id) {
	return "https://graph.facebook.com/" . $id;
}

public function getFeed($id) {
	$url = $this->getGraphUrl($id)
		. "/feed?access_token="
		. $this->_accessToken;
	$ch = curl_init($url);
	$options = array(CURLOPT_RETURNTRANSFER => true);
	curl_setopt_array($ch, $options);
	$result = json_decode(curl_exec($ch));

	return $result;
}

/**
 * Converts the given DomEl into a facebook like button, with 
 */
public function like($domElement, $width = 450, $showFaces = true) {
	$this->checkSdk();
	$showFaces = !!$showFaces
		? "true"
		: "false";
	$fbDiv = $this->_dom->create("div", array(
		"class" 			=> "fb-like",
		"data-send"			=> "true",
		"data-width"		=> $width,
		"data-show-faces"	=> $showFaces
	));

	$domElement->replace($fbDiv);
}

public function showComments($domElement, $width = 470, $numPosts = 2) {
	$this->checkSdk();
	$fbDiv = $this->_dom->create("fb:comments", array(
		"href"		=> URL,
		"num-posts"	=> $numPosts,
		"width"		=> $width
	));

	$domElement->replace($fbDiv);
}

}#