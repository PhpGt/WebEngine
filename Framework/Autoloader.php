<?php spl_autoload_register(function($className) {
/**
* Utility classes are small and uncommon code libraries. Typically, a utility
* class does not complete a whole task on its own, otherwise it would be classed
* as a 'tool'.
*
* The point in the utility classes here are to provide object-oriented
* enhancements to PHP. Certain areas of PHP can be enhanced by wrapping in
* objects, and new features can be introduced.
*/

if(class_exists("ClassDependencies")
&& array_key_exists($className, ClassDependencies::$list)) {
	$relPath = ClassDependencies::$list[$className];
	require_once(GTROOT . "/Class/$relPath");
	return;
}

$composerAutoloader = "/vendor/autoload.php";
$composerSearchPaths = array(
	APPROOT . "/Class",
	GTROOT . "/Class",
	);
foreach ($composerSearchPaths as $root) {
	$pathToAutoloader = $root . $composerAutoloader;
	if(file_exists($pathToAutoloader)) {
		require_once($pathToAutoloader);
	}
}

$configSuffix = "_Config";
if(substr($className, -strlen($configSuffix)) === $configSuffix) {
	$configFile = APPROOT . "/Config/$className.cfg.php";

	if(file_exists($configFile) && !class_exists($className)) {
		require_once($configFile);
		return;
	}
}

$classDirArray = array(
	APPROOT . "/Class/$className",
	GTROOT  . "/Class/$className", 
);
if(strstr($className, "_")) {
	$splitClassName = substr($className, 0, strpos($className, "_"));
	$classDirArray[] = APPROOT . "/Class/$splitClassName";
	$classDirArray[] = GTROOT  . "/Class/$splitClassName";
}
foreach ($classDirArray as $classDir) {
	$fileNameArray = array($className . ".class.php");
	$fileNameArray[] = str_replace("_", ".", $fileNameArray[0]);

	foreach($fileNameArray as $fileName) {
		if(file_exists("$classDir/$fileName")) {
			require_once("$classDir/$fileName");
			return;
		}		
	}
}

});#