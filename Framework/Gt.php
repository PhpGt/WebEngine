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
 *
 * For unit tests, passing in true to $skipRequestResponse will stop the usual
 * request-response execution.
 */
public function __construct($t, $skipRequestResponse = false) {
	// set_error_handler(array("ErrorHandler", "error"), 
	// 	E_ALL & E_NOTICE & E_RECOVERABLE_ERROR);

	$baseSuffix = "_Framework";
	$appConfigClass = "App_Config";
	$databaseConfigClass = "Database_Config";
	$securityConfigClass = "Security_Config";

	if(!class_exists($appConfigClass)) {
		class_alias($appConfigClass . $baseSuffix, $appConfigClass);
		$appConfigClass .= $baseSuffix;
	}
	if(!class_exists($databaseConfigClass)) {
		class_alias($databaseConfigClass . $baseSuffix, $databaseConfigClass);
		$databaseConfigClass .= $baseSuffix;
	}
	if(!class_exists($securityConfigClass)) {
		class_alias($securityConfigClass . $baseSuffix, $securityConfigClass);
		$securityConfigClass .= $baseSuffix;
	}
	
	$config = array(
		"App"       => $appConfigClass,
		"Database"  => $databaseConfigClass,
		"Security"  => $securityConfigClass,
	);
	foreach ($config as $c) {
		$c::init();
	}

	if($skipRequestResponse) {
		return;
	}

	try {
		// Compute the request, instantiating the relavent PageCode/Api.
		$request       = new Request($config, $t);
		$response      = new Response($request);

		// Execute the page lifecycle from the Dispatcher.
		return new Dispatcher($response, $config);

	} catch (Exception $e) {
		$handler = new GtExceptionHandler();
		$handler->handle($e);
	}
}

}#