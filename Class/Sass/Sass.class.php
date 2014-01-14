<?php class Sass {
private $_filePath;
private $_scssc;

public function __construct($filePath) {
	$filePath = preg_replace("/\/+/", "/", $filePath);
	if(!file_exists($filePath)) {
		return false;
	}

	$this->_filePath = $filePath;
}

public function parse() {
	$this->_scssc = new scssc();
	$contents = file_get_contents($this->_filePath);
	$this->_scssc->addImportPath(dirname($this->_filePath));
	$parsedString = $this->_scssc->compile($contents);
	return $parsedString;
}

}#