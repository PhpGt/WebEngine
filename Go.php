<?php 
/**
 * PHP.Gt can be served with or without a webserver being present. If this 
 * script is run from the command line, it will act as its own webserver.
 */

$serverPattern = "/^\/(?:(?:[^\.^\?]+)(?:\.html|\.json)?)?(?:\?(.*))?$/i";
$bootstrap = __DIR__ . "/Framework/Bootstrap.php";
$defaultContentType = "text/plain";
$contentTypeOverrideArray = [
	"css" => "text/css",
	"js" => "application/javascript",
];

if(php_sapi_name() == "cli-server") {
	$cwd = getcwd();
	if(substr($cwd, -strlen($cwd)) == "PHP.Gt") {
		chdir("..");
		$cwd = getcwd();
	}

	$appName = strtok($_SERVER["HTTP_HOST"], ".");
	if(is_dir("$appName/www")) {
		chdir("$appName/www");
		$cwd = getcwd();
		$_SERVER["DOCUMENT_ROOT"] = $cwd;
	}

	if(preg_match($serverPattern, $_SERVER["REQUEST_URI"], $matches)) {
		require $bootstrap;
		return true;
	}
	else {
		$request = explode("?",
			$_SERVER["DOCUMENT_ROOT"] . $_SERVER["REQUEST_URI"]
		);

		if(is_file($request[0])) {
			$ext = pathinfo($request[0], PATHINFO_EXTENSION);
			$mime = $defaultContentType;

			if(array_key_exists($ext, $contentTypeOverrideArray)) {
				$mime = $contentTypeOverrideArray[$ext];
			}
			else {
				$finfo = new Finfo(FILEINFO_MIME_TYPE);
				$mime = $finfo->file($request[0]);				
			}

			if(false !== $mime) {
				header("Content-type: $mime");
				$fullPath = $request[0];
				if(isset($request[1])) {
					$fullPath = "?" . $request[1];
				}

				readfile($request[0]);
				return true;				
			}
		}

		return false;
	}		
}
else if(php_sapi_name() == "cli") {

}
else {
	require $bootstrap;
}