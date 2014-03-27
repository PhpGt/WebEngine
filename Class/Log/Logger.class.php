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
private $_logLevel = 5;
private $_classWhiteList = array();
private $_classBlackList = array();
private $_name;
private $_file;
private $_path = APPROOT;
private $_datePattern = "Y-m-d H:i:s";
private $_messageFormat = 
	"%DATETIME% %LEVEL% [%CLASS%, %FILE% :%LINE%]\t%MESSAGE%\n";
private $_messageEnd = "\n";

private $_defaults = array(
	"logLevel" => 5,
	"classWhiteList" => array(),
	"classBlackList" => array(),
	"path" => APPROOT,
	"datePattern" => "Y-m-d H:i:s",
	"messageFormat" => 
		"%DATETIME% %LEVEL% [%CLASS%, %FILE% :%LINE%]\t%MESSAGE%\n",
);

public function __construct($name, $config) {
	$this->_name = ucfirst($name);
	$this->_file = "$name.log";
	$this->_path = APPROOT;

	// Import config variables here if they exist.
	if(!empty($config)) {
		foreach ($config as $key => $value) {
			$var = "_$key";
			$this->$var = $value;
		}
	}
	else if(class_exists("Log_Config")) {
		$this->importConfig();
		if(isset(Log_Config::$logLevel)) {
			$this->_logLevel = Log_Config::$logLevel;			
		}
		if(isset(Log_Config::$path)) {
			$this->_path = Log_Config::$path;			
		}
		if(isset(Log_Config::$datePattern)) {
			$this->_datePattern = Log_Config::$datePattern;			
		}
	}
}

private function importConfig() {
	// Load all members from Config object over this object's members, ignoring
	// those stored in skipMembers.
	$members = get_class_vars(__CLASS__);
	$skipMembers = array("levels", "name");
	$configMemberArray = get_class_vars("Log_Config");
	foreach ($members as $member => $value) {
		if(in_array($member, $skipMembers)) {
			continue;
		}

		if(isset($_this->_defaults[$member])) {
			$this->_{$member} = $this->_defaults[$member];			
		}
	}

	foreach ($configMemberArray as $member => $value) {
		if(in_array($member, $skipMembers)) {
			continue;
		}


		if(array_key_exists("_$member", $members)) {
			$var = "_$member";
			$this->$var = $value;
		}
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
	$logLevel = $this->_logLevel;
	if(is_string($logLevel)) {
		$logLevel = array_search($logLevel, $this->_levels);
	}

	// Level is the current level of the log being made, 
	// from 0: FATAL to 5: TRACE.
	// LogLevel is the minimum-allowed log to be made.
	if($level > $logLevel) {
		return false;
	}

	$logLine = $this->_messageFormat;

	$logLine = str_replace("%DATETIME%", date($this->_datePattern), $logLine);
	$logLine = str_replace("%LEVEL%", $this->_levels[$level], $logLine);
	$logLine = str_replace("%FILE%", $backtrace[0]["file"], $logLine);
	$logLine = str_replace("%LINE%", $backtrace[0]["line"], $logLine);
	$logLine = str_replace("%CLASS%", $backtrace[2]["class"], $logLine);
	$logLine = str_replace("%MESSAGE%", $msg, $logLine);
	$logLine = str_replace("%IP%", $_SERVER["REMOTE_ADDR"], $logLine);
	$logLine = str_replace("%PORT%", $_SERVER["REMOTE_PORT"], $logLine);
	$logLine = str_replace("%REFERER%", $_SERVER["HTTP_REFERER"], $logLine);
	if(!is_null($throwable)) {
		str_replace("%EXCEPTION%", $throwable->getMessage());
	}

	if(!empty($this->_classWhiteList)) {
		$whiteListValid = in_array(
			$backtrace[2]["class"], $this->_classWhiteList);

		if($whiteListValid === false) {
			return false;
		}
	}
	else if(!empty($this->_classBlackList)) {
		$blackListInvalid = in_array(
			$backtrace[2]["class"], $this->_classBlackList);

		if($blackListInvalid === true) {
			return false;
		}
	}

	return false !== file_put_contents(
		$this->_path . "/" . $this->_file, $logLine, FILE_APPEND);
}

}#