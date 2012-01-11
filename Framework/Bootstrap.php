<?php
/**
* The bootstrapper performs two vital operations:
* 1) Declares all named variables and sets the environment, ready for...
* 2) The loading of all required files.
* PHP.Gt uses dependency injection throughout - so only the bare minimum is
* loaded by the bootstrapper.
* This is the only procedural-style code in the whole framework. The last line
* of the bootstrapper creates a new instance of Gt, the core object.
*/
// Directory is in Web root of current application.
// Move one directory up to get the "app root".
chdir("..");
$cwd = substr(getcwd(), strrpos(getcwd(), "/") + 1);
// Get the requested path, remove initial slash and any query strings.
$path = substr($_SERVER["REQUEST_URI"], 1);
if(strpos($path, "?") !== false) {
	$path = substr($path, 0, strpos($path, "?"));
}
$pathInfo = pathinfo($path);
if(!isset($pathInfo["dirname"])) { $pathInfo["dirname"] = ""; }
if(!isset($pathInfo["extension"])) { $pathInfo["extension"] = ""; }
if($pathInfo["dirname"] === ".") { $pathInfo["dirname"] = ""; }
$pathInfo["filename"] = preg_replace("/(\?|&).*/", "", $pathInfo["filename"]);

// Obtain information about the requested file and directory from path info.
$dirName = "";
if(!empty($pathInfo["dirname"])) {
	$dirName = $pathInfo["dirname"];
}
else {
	if(empty($pathInfo["extension"])) {
		$dirName = $pathInfo["filename"];
	}
}
if(empty($pathInfo["extension"]) ) {
	if(!empty($pathInfo["dirname"])) {
		$dirName .= "/" . $pathInfo["filename"];
	}
}

$fileName = "Index";
if(!empty($pathInfo["filename"])) {
	if(!empty($pathInfo["extension"])) {
		$fileName = trim($pathInfo["filename"]);
	}
}


$extension = (isset($pathInfo["extension"]) )
	? trim($pathInfo["extension"])
	: $_GET["Ext"];
if(empty($extension) && $fileName == "Index") {
	$extension = "html";
}

// Redirect incorrectly requested extensions to always use .html.
if($extension == "htm") {
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $fileName.html");
	exit;
}

// Over HTTP, slashes are always forward - no need for DS.
$slashPos = strpos($dirName, "/");
$baseDir = ($slashPos === false)
	? (strlen($dirName) === 0
		? ""
		: $dirName)
	: substr($dirName, 0, $slashPos);

// For finding the correct PageCode and PageView, on different systems.
$dirPath = str_replace("/", DS, $dirName);
$filePath = $dirName . DS . $fileName;
while($filePath[0] == DS) { 
	$filePath = substr($filePath, 1);
}
$fileClass = str_replace(DS, "_", $filePath);

define("VER",        "1.0");
define("APPNAME",    substr($cwd, 0, strrpos($cwd, ".")) );
define("GTROOT",     dirname(dirname(__FILE__)));
define("APPROOT",    getcwd());
define("DIR",        $dirName);
define("BASEDIR",    $baseDir);
define("FILE",       $fileName);
define("EXT",        $extension);
define("DIRPATH",    $dirPath);
define("FILEPATH",   $filePath);
define("FILECLASS",  $fileClass);

// Define the minimum required files to run the framework. The path of each
// requirement can be an array of paths, in order of priority (for version 
// compatibility).
$required = array(
	"Shared application config" => 
	GTROOT . DS . "Config" . DS . "App.cfg.php",
	"Shared database config" =>
	GTROOT . DS . "Config" . DS . "Database.cfg.php",
	"Shared security config" =>
	GTROOT . DS . "Config" . DS . "Security.cfg.php",

	"Application-specific application config" =>
	APPROOT . DS . "Config" . DS . "App.cfg.php",
	"Application-specific database config" => 
	APPROOT . DS . "Config" . DS . "Database.cfg.php",
	"Application-specific security config" => 
	APPROOT . DS . "Config" . DS . "Security.cfg.php",

	"API component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "Api.cmp.php",
	"API wrapper componenet" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "ApiWrapper.cmp.php",
	"API element component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "ApiEl.cmp.php",
	"DAL component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "Dal.cmp.php",
	"DAL element component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "DalEl.cmp.php", 
	"DAL result component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "DalResult.cmp.php",
	"DOM component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "Dom.cmp.php",
	"DOM Element component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "DomEl.cmp.php",
	"DOM Element Collection component" =>
	GTROOT . DS . "Framework" . DS . "Component" . DS . "DomElCollection.php",
	"Error component" => 
	GTROOT . DS . "Framework" . DS . "Component" . DS . "Error.cmp.php",

	"File Organiser class" =>
	GTROOT . DS . "Framework" . DS . "FileOrganiser.php",
	"Injector class" =>
	GTROOT . DS . "Framework" . DS . "Injector.php",
	"Request class" =>
	GTROOT . DS . "Framework" . DS . "Request.php",
	"Response class" =>
	GTROOT . DS . "Framework" . DS . "Response.php",
	"Dispatcher class" =>
	GTROOT . DS . "Framework" . DS . "Dispatcher.php",
	"PageCode class" =>
	GTROOT . DS . "Framework" . DS . "PageCode.php",

	"Main PHP.Gt object" =>
	GTROOT . DS . "Framework" . DS . "Gt.php"
);
foreach($required as $title => $path) {
	if(!is_array($path)) { $path = array($path); }
	$pathLen = count($path);
	for($i = 0; $i < $pathLen; $i++) {
		if(file_exists($path[$i])) {
			require($path[$i]);
		}
		else {
			if($i === $pathLen - 1) {
				die("PHP.Gt cannot load, the $title file cannot be found.");
			}
		}
	}
}

if(isset($_GET["DebugBootstrap"])) {
	// If you don't trust your webserver has been set up correctly, have a look 
	// through the output of this code block:

	echo "PathInfo: " . PHP_EOL;
	var_dump($pathInfo);

	echo "Constants: " . PHP_EOL;
	echo "<pre>";
	echo "CWD: "      . $cwd      . PHP_EOL 
	. "VER: "      . VER       . PHP_EOL
	. "APPNAME: "  . APPNAME   . PHP_EOL
	. "GTROOT: "   . GTROOT    . PHP_EOL
	. "APPROOT: "  . APPROOT   . PHP_EOL
	. "DS: "       . DS        . PHP_EOL 
	. PHP_EOL
	. "DIR: "      . DIR       . PHP_EOL
	. "BASEDIR: "  . BASEDIR   . PHP_EOL
	. "FILE: "     . FILE      . PHP_EOL
	. "EXT: "      . EXT       . PHP_EOL
	. "DIRPATH: "  . DIRPATH   . PHP_EOL
	. "FILEPATH: " . FILEPATH  . PHP_EOL
	. "FILECLASS: ". FILECLASS . PHP_EOL
	. PHP_EOL;
	echo "</pre>";

	echo "GET: " . PHP_EOL;
	var_dump($_GET);
	exit;
}

$gt = new Gt();
?>