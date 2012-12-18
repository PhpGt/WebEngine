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
	APPROOT .DS . "Class" . DS . $className . DS,
	GTROOT . DS . "Class" . DS . $className . DS 
);
foreach ($classDirArray as $classDir) {
	$fileName = $className . ".class.php";
	if(file_exists($classDir . $fileName)) {
		require_once($classDir . $fileName);
		return;
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