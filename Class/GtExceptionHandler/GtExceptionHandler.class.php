<?php 
/**
 * This is a default exception handler, used by at the top of the PHP.Gt
 * request/ response chain to process any exceptions that get this far.
 * 
 * It can be overridden with a project-specific file by creating one of the
 * same name in the project's classpath.  PHP.Gt will then use that version
 * INSTEAD of this one.
 */

class GtExceptionHandler {

public function handle(\Exception $e) {
	$log = Log::get();
	$log->error($e);
	// put it in the webserver log too
	error_log($e);

	if(App_Config::isProduction()) {
		new HttpError(
			500,
			[
				"Message"	=> "An error has occurred.  It has been logged, "
					. "but please let us know if it happens again or is "
					. "causing you problems.",
				"Number"	=> $e->getCode(),
			]
			);

	} else {
		// use the HttpError class to write-out the exception details
		new HttpError(
			500, 
			[
				"Message"	=> $e->getMessage(),
				"Number"	=> $e->getCode(),
				"File"		=> $e->getFile(),
				"Line"		=> $e->getLine(),
				"Context"	=> $e->getTraceAsString(),
			]
			);
	}
}
}#