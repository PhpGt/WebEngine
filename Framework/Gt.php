<?php final class Gt {
/**
 * The Bootstrap script's final job is to instantiate the Gt object. Gt acts as
 * the parent object to everything in the whole request/response, but is in fact
 * quite simple.
 *
 * The first task is to load the application's settings files, or provide
 * defaults for when there aren't any. Then the request is handled, followed by
 * the response. The final task is to compute all code and render the page. This
 * is done by the Dispatcher.
 */
public function __construct() {
	set_error_handler(array("ErrorHandler", "error"), 
		E_ALL & E_NOTICE & E_RECOVERABLE_ERROR);

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
	return new Dispatcher($response, $config);
}

}#