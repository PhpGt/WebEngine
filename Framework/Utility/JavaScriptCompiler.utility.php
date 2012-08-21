<?php final class JavaScriptCompiler_Utility {
/**
 * TODO: Docs.
 */
private $_script;

public function __construct($javaScript) {
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
}

public function output() {
return $this->_script;
}

}?>