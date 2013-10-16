<?php class Logger {
/**
 * The Logger class outputs different levels of log message to the named log
 * file in the application's root directory.
 */

private $_levels = array(
	"FATAL", // Severe events, typically causing the application to abort.
	"ERROR", // Non-fatal, but high-importance errors.
	"WARN",  // Potentially harmful situations.
	"INFO",  // Abstract informational messages; progress or application status.
	"DEBUG", // Low-level abstraction of debugging information.
	"TRACE", // Low-level, high-grain logging.
);
private $_name;
private $_file;
private $_path;
private $_datePattern = "Y-m-d H:i:s";
private $_messageFormat = "%DATETIME% %LEVEL% [%FILE% :%LINE%]\t%MESSAGE%\n";
private $_messageEnd = "\n";

public function __construct($name) {
	$this->_name = ucfirst($name);
	$this->_file = "$name.log";
	$this->_path = APPROOT;

	if(class_exists("Logger_Config")) {
		// Import config variables here if they exist.
		// TODO: Path may want to be /var/log/lighttpd or /var/log/phpgt/appname
	}
}

public function getName() {
	return $this->_name;
}

/**
 * Allows calling fatal, error, warn, etc. on this object
 */
public function __call($name, $args) {
	$key = strtoupper($name);
	$levelKey = array_search($key, $this->_levels);
	if($levelKey !== false) {
		$backtrace = debug_backtrace();
		$params = array_merge([$backtrace, $levelKey], $args);
		return call_user_func_array([$this, "log"], $params);
	}

	throw new BadMethodCallException();
}

private function log($backtrace, $level, $msg, $throwable = null) {
	$logLine = $this->_messageFormat;

	$logLine = str_replace("%DATETIME%", date($this->_datePattern), $logLine);
	$logLine = str_replace("%LEVEL%", $this->_levels[$level], $logLine);
	$logLine = str_replace("%FILE%", $backtrace[0]["file"], $logLine);
	$logLine = str_replace("%LINE%", $backtrace[0]["line"], $logLine);
	$logLine = str_replace("%MESSAGE%", $msg, $logLine);
	if(!is_null($throwable)) {
		str_replace("%EXCEPTION%", $throwable->getMessage());
	}

	return false !== file_put_contents(
		$this->_path . "/" . $this->_file, $logLine, FILE_APPEND);
}

}#