<?php
/**
 * Router script used by gtserver shell script.
 *
 * Will serve all directory-style URLs through PHP.Gt. Files (URLs with 
 * file extensions) will have their bytes streamed and correct HTTP headers set.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
if(php_sapi_name() !== "cli-server") {
	echo "ERROR: Script must be called from cli-server.\n";
	exit(1);
}

$pathinfo = pathinfo($_SERVER["REQUEST_URI"]);
if(!empty($pathinfo["extension"])) {
	// Non-empty extension is served as static file.
	$request = explode("?", 
		$_SERVER["DOCUMENT_ROOT"]
		.
		$_SERVER["REQUEST_URI"]
	);

	if(!is_file($request[0])) {
		return false;
	}

	$ext = pathinfo($request[0], PATHINFO_EXTENSION);
	$finfo = new Finfo(FILEINFO_MIME_TYPE);
	$mime = $finfo->file($request[0]);

	header("Content-type: $mime");
	readfile($request[0]);
	return true;
}

// Request is to a PHP.Gt PageView or WebService.
// TODO: Pass to the PHP.Gt bootstrapper.