<?php final class SassParser_Utility {
private $_filePath;
private $_sassParser;

public function __construct($filePath) {
	require_once(GTROOT . DS . "Framework" . DS . "Utility" . DS . "Sass"
		. DS . "SassParser.php");
	if(!file_exists($filePath)) {
		return false;
	}

	$this->_filePath = $filePath;
}

public function parse() {
	$this->_sassParser = new SassParser();
	$parsedString = $this->_sassParser->toCss($this->_filePath);
	return $parsedString;
}

}?>