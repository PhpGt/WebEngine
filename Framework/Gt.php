<?php
/**
* TODO: Docs.
*/
final class Gt {
	public function __construct() {
		$config = array(
			"App"       => new App_Config(),
			"Database"  => new Database_Config(),
			"Security"  => new Security_Config()
		);

		// Compute the request, instantiating the relavent PageCode/Api.
		$request       = new Request($config);
		$response      = new Response($request);

		// Execute the page lifecycle from the Dispatcher.
		new Dispatcher($response, $config);
	}
}

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
}
?>