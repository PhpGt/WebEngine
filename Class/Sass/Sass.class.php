<?php class Sass {
private $_filePath;
private $_sassParser;

public function __construct($filePath) {
	require_once("SassParser/SassParser.php");
	$filePath = preg_replace("/\/+/", "/", $filePath);
	if(!file_exists($filePath)) {
		return false;
	}

	$this->_filePath = $filePath;
}

public function parse() {
	$this->_sassParser = new SassParser();
	$this->_sassParser->debug_info = !App_Config::isProduction();
	
	$parsedString = $this->_sassParser->toCss($this->_filePath);
	return $parsedString;
}

}#