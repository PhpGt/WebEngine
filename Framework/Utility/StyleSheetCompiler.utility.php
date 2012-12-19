<?php final class StyleSheetCompiler_Utility {
/**
 * TODO: Docs.
 */
private $_css;

public function __construct($css) {
	// TODO: Minify the CSS before storing.
	$this->_css = $css;
}

public function output() {
	return $this->_script;
}

}?>