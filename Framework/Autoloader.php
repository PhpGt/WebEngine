<?php function __autoload($className) {
/**
* Utilities are small and uncommon code libraries. Typically, a utility does not
* complete a single task on its own, otherwise it would be classed as a 'tool'.
* Autoloading is not heavily relied upon within PHP.Gt as there is usually a 
* logical location for all files to reside, but generic utilities are an
* exception. Because utilities are uncommonly used (otherwise they would become
* PageTools), to avoid having an overhead of loading utilities, the PHP
* autoloader is used to require the utility file.
*/
$utilityDir = GTROOT . DS . "Framework" . DS . "Utility" . DS;
$fileName = str_replace("_", ".", $className . ".php");
if($dh = opendir($utilityDir)) {
	while(false !== ($file = readdir($dh)) ) {
		if(stristr($file, $fileName)) {
			require $utilityDir . $file;
			break;
		}
	}
}
else {
	// TODO: Proper error log and output.
	die("Failed to open utility directory.");
}

}?>