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
// Directory is in Web root of current application...
// Move one directory up to get the "app root".
$startTime = microtime(true);
if(!defined("DS")) {
	define("DS", DIRECTORY_SEPARATOR);
}
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
	: "";
if(empty($extension) && $fileName == "Index") {
	$extension = "html";
}

// Redirect incorrectly requested extensions to always use .html.
if($extension == "htm" || (strtolower($extension) != $extension &&
(strtolower($extension) == "htm" || strtolower($extension) == "html") )) {
	http_response_code(301);
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
$fileClass = str_replace("-", "", $fileClass);
$fileClass = $fileClass . "_PageCode";

$appName = strstr($cwd, ".")
	? substr($cwd, 0, strrpos($cwd, "."))
	: $cwd;

$pageUrl = (empty($_SERVER["HTTPS"]))
	? "http://" 
	: "https://";
if($_SERVER["SERVER_PORT"] != "80") {
	$pageUrl .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"]
	. $_SERVER["REQUEST_URI"];
}
else {
	$pageUrl .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
}

if(empty($dirName)) {
	$path = "$fileName";	
}
else {
	$path = "$dirName/$fileName";
}

define("URL",		$pageUrl);
define("VER",		"1.0");
define("APPNAME",	$appName);
define("GTROOT",	dirname(dirname(__FILE__)));
define("APPROOT",	getcwd());
define("DIR",		$dirName);
define("BASEDIR",	$baseDir);
define("PATH",		$path);
define("FILE",		$fileName);
define("EXT",		$extension);
define("FILECLASS",	$fileClass);
define("FILEPATH",	$filePath);

// Define the minimum required files to run the framework. The path of each
// requirement can be an array of paths, in order of priority (for version 
// compatibility).
$toLoad = array();
$toLoad["Required"] = array(
	"Config interface" =>
		GTROOT."/Config/Config.cfg.php",
	"Shared application config" => 
		GTROOT."/Config/App.cfg.php",
	"Shared database config" => 
		GTROOT."/Config/Database.cfg.php",
	"Shared security config" =>	
		GTROOT."/Config/Security.cfg.php",

	"Error Exception" => 
		GTROOT."/Framework/Error/Error.php",
	"Error Handler" => 
		GTROOT."/Framework/Error/ErrorHandler.php",
	"Http Error Exception" => 
		GTROOT."/Framework/Error/HttpError.php",

	"API component" => 
		GTROOT."/Framework/Component/Api.php",
	"API wrapper component" => 
		GTROOT."/Framework/Component/ApiWrapper.php",
	"API element component" => 
		GTROOT."/Framework/Component/ApiEl.php",
	"Template wrapper component" => 
		GTROOT."/Framework/Component/TemplateWrapper.php",
	"PageTool wrapper component" =>
		GTROOT."/Framework/Component/PageToolWrapper.php",
	"DAL component" =>
		GTROOT."/Framework/Component/Dal.php",
	"DAL element component" =>
		GTROOT."/Framework/Component/DalEl.php", 
	"DAL result component" =>
		GTROOT."/Framework/Component/DalResult.php",
	"DOM component" =>
		GTROOT."/Framework/Component/Dom.php",
	"DOM Element component" =>
		GTROOT."/Framework/Component/DomEl.php",
	"DOM Element Collection component" =>
		GTROOT."/Framework/Component/DomElCollection.php",
	"DOM Element ClassList component" =>
		GTROOT."/Framework/Component/DomElClassList.php",
	"Cache component" =>
		GTROOT."/Framework/Component/Cache.php",

	"Autoloader" =>
		GTROOT."/Framework/Autoloader.php",
	"ClassDependencies" =>
		GTROOT."/Class/ClassDependencies.php",
	"File Organiser class" =>
		GTROOT."/Framework/FileOrganiser.php",
	"Client side compiler class" =>
		GTROOT."/Framework/ClientSideCompiler.php",
	"Manifest class" =>
		GTROOT."/Framework/Manifest.php",
	"Request class" =>
		GTROOT."/Framework/Request.php",
	"Response class" =>
		GTROOT."/Framework/Response.php",
	"Dispatcher class" =>
		GTROOT."/Framework/Dispatcher.php",
	"PageTool class" =>
		GTROOT."/Framework/PageTool.php",
	"PageCode class" =>
		GTROOT."/Framework/PageCode.php",

	"Empty object" =>
		GTROOT."/Framework/EmptyObject.php",
	"Main PHP.Gt object" =>
		GTROOT."/Framework/Gt.php",
	"PHP.Gt's Composer autoloader file" =>
		GTROOT . "/Class/vendor/autoload.php",
);

$toLoad["Optional"] = array(
	"Application-specific application config" =>
		APPROOT."/Config/App.cfg.php",
	"Application-specific database config" => 
		APPROOT."/Config/Database.cfg.php",
	"Application-specific security config" => 
		APPROOT."/Config/Security.cfg.php",
	"The application's Composer autoloader file" =>
		APPROOT . "/Class/vendor/autoload.php",
);

foreach($toLoad as $requirement => $loadArray) {
	foreach($loadArray as $title => $path) {
		if(!is_array($path)) { $path = array($path); }
		$pathLen = count($path);
		for($i = 0; $i < $pathLen; $i++) {
			if(file_exists($path[$i])) {
				require($path[$i]);
			}
			else {
				if($requirement === "Optional") {
					continue;
				} 
				
				if($i === $pathLen - 1) {
					die("PHP.Gt cannot load, the $title file cannot be found.");
				}
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
	echo "CWD: "	. $cwd		. PHP_EOL 
	. "VER: "		. VER		. PHP_EOL
	. "APPNAME: "	. APPNAME	. PHP_EOL
	. "GTROOT: "	. GTROOT	. PHP_EOL
	. "APPROOT: "	. APPROOT	. PHP_EOL
	. "DS: "		. DS		. PHP_EOL 
	. PHP_EOL
	. "DIR: "		. DIR		. PHP_EOL
	. "BASEDIR: "	. BASEDIR	. PHP_EOL
	. "FILE: "		. FILE		. PHP_EOL
	. "EXT: "		. EXT		. PHP_EOL
	. "DIRPATH: "	. DIRPATH	. PHP_EOL
	. "FILEPATH: "	. FILEPATH	. PHP_EOL
	. "FILECLASS: "	. FILECLASS	. PHP_EOL
	. PHP_EOL;
	echo "</pre>";

	echo "GET: "	. PHP_EOL;
	var_dump($_GET);
	exit;
}

return new Gt($startTime);
?>