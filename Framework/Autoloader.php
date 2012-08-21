<?php function __autoload($className) {
/**
* TODO: Docs.
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