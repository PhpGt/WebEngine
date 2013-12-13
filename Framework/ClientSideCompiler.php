<?php final class ClientSideCompiler {
/**
 * The ClientSideCompiler minifies/obfuscates source files.
 */

const CACHEFILE = "Compiled.cache";

private static $_processMatches = array(
	"/\.scss$/" => ".css",
);

/**
 * Perform the processing of files that require server-side processing. This
 * does not include a minification/obfuscation step; only the processing and 
 * expansion of files such as Sass CSS or JavaScript files with server-side 
 * includes is processed.
 *
 * This function calls separate functions for each file type to process. This
 * allows for easy future development.
 *
 * @param string $sourcePath Absolute path to source file on disk ready to 
 * process.
 *
 * Returns a string representing the processed file's contents, ready to be 
 * written to disk.
 */
public static function process($sourcePath) {
	if(!file_exists($sourcePath)) {
		throw new Exception("Attempt to process missing file: $sourcePath");
	}

	$ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
	$ext = trim(strtolower($ext));
	$processMethod = "process_$ext";

	if(method_exists("ClientSideCompiler", $processMethod)) {
		return ClientSideCompiler::$processMethod($sourcePath);
	}

	return file_get_contents($sourcePath);
}

/**
 * Recursive function. When true is passed as recurse, will return just the
 * text content of the required JavaScript, including any sub-requires.
 */
private static function process_js($sourcePath, $recurse = false) {
	$contents = "";

	if(!file_exists($sourcePath)) {
		throw new Exception(
			"Attempting to process non-existant js file $sourcePath");
	}

	$fh = fopen($sourcePath, "r");
	while(false !== ($line = fgets($fh)) ) {
		$lineTrim = trim($line);
		
		if(strpos($lineTrim, "//= require_tree") === 0) {
			$path = substr($lineTrim, strlen("//= require_tree") + 1);
			if($path[0] == "/") {
				$path = APPROOT . $path;
			}
			else {
				$path = dirname($sourcePath) . "/" . $path;
			}

			// TODO: Recursive directory requirement.
			$files = scandir($path);
			foreach ($files as $f) {
				if($f[0] == ".") {
					continue;
				}

				$contents .= self::process_js("$path/$f", true) . "\n";
			}
		}
		else if(strpos($lineTrim, "//= require") === 0) {
			$path = substr($lineTrim, strlen("//= require") + 1);
			if($path[0] == "/") {
				$path = APPROOT . $path;
			}
			else {
				$path = dirname($sourcePath) . "/" . $path;
			}

			$contents .= self::process_js($path, true) . "\n";
		}
		else {
			$contents .= $line;
		}
	}
	fclose($fh);

	return $contents;
}

private static function process_scss($sourcePath) {
	$sass = new Sass($sourcePath);
	return $sass->parse();
}

}#