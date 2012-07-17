<?php final class Gt {
public function __construct() {
	$baseSuffix = "_Framework";
	$appConfigClass = "App_Config";
	$databaseConfigClass = "Database_Config";
	$securityConfigClass = "Security_Config";

	if(!class_exists($appConfigClass)) {
		$appConfigClass .= $baseSuffix;
	}
	if(!class_exists($databaseConfigClass)) {
		$databaseConfigClass .= $baseSuffix;
	}
	if(!class_exists($securityConfigClass)) {
		$securityConfigClass .= $baseSuffix;
	}
	
	$config = array(
		"App"       => new $appConfigClass(),
		"Database"  => new $databaseConfigClass(),
		"Security"  => new $securityConfigClass()
	);

	// Compute the request, instantiating the relavent PageCode/Api.
	$request       = new Request($config);
	$response      = new Response($request);

	// Execute the page lifecycle from the Dispatcher.
	new Dispatcher($response, $config);
}

}

// TODO: Move this into separate file to allow for Gt-style end braces.
/**
* TODO: Docs.
*/
function __autoload($className) {
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