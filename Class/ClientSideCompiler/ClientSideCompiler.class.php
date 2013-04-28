<?php class JavaScriptCompiler {
/**
 * 
 */
private $_script;
private $_style;

public function __construct() {
}

public function javaScript($javaScript) {
	// TODO: Use HTTP class for OOP tidyness.
	$ch = curl_init("http://closure-compiler.appspot.com/compile");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
		"output_info=compiled_code"
			. "&output_format=text"
			// SIMPLE_OPTIMIZATIONS | ADVANCED_OPTIMIZATIONS
			. "&compilation_level=SIMPLE_OPTIMIZATIONS"
			. "&js_code=" . urlencode($javaScript)
	);

	$curlResult = curl_exec($ch);
	curl_close($ch);

	if($curlResult !== false) {
		$this->_script = $curlResult;
	}

	return $this->_script;
}

public function styleSheet($styleSheet) {
	// TODO: Minify the CSS before outputting it.
	$this->_style = $styleSheet;
	return $this->_style;
}

}#