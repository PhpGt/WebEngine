<?php function __autoload($className) {
/**
* Utility classes are small and uncommon code libraries. Typically, a utility
* class does not complete a whole task on its own, otherwise it would be classed
* as a 'tool'.
*
* The point in the utility classes here are to provide object-oriented
* enhancements to PHP. Certain areas of PHP can be enhanced by wrapping in
* objects, and new features can be introduced.
*/

// ALPHA TODO: Before first beta version, remove the dependency to the old
// Utility directory.
$classDirArray = array(
	APPROOT .DS . "Class" . DS . $className,
	GTROOT . DS . "Class" . DS . $className 
);
if(strstr($className, "_")) {
	$splitClassName = substr($className, 0, strpos($className, "_"));
	$classDirArray[] = APPROOT . DS . "Class" . DS . $splitClassName;
	$classDirArray[] = GTROOT . DS . "Class" . DS . $splitClassName;
}
foreach ($classDirArray as $classDir) {
	$fileNameArray = array($className . ".class.php");
	$fileNameArray[] = str_replace("_", ".", $fileNameArray[0]);


	foreach($fileNameArray as $fileName) {
		if(file_exists($classDir . DS . $fileName)) {
			require_once($classDir . DS . $fileName);
			return;
		}		
	}
}

$utilityDir = GTROOT . DS . "Framework" . DS . "Utility" . DS;
$fileName = str_replace("_", ".", $className . ".php");
if($dh = opendir($utilityDir)) {
	while(false !== ($file = readdir($dh)) ) {
		if(stristr($file, $fileName)) {
			require_once($utilityDir . $file);
			break;
		}
	}
	closedir($dh);
}

}?>