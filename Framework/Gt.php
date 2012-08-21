<?php final class Gt {
/**
 * TODO: Docs.
 */
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

}?>