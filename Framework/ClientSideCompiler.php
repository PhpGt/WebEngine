<?php final class ClientSideCompiler {

public static $sourceMap = [
	"/^(.+)\.scss$/" => "\$1.css",
];

/**
 * Loops over all matching patterns in the sourceMap above and replaces the
 * given source with the corresponding replacement.
 *
 * Used to remove filenames that browsers can't understand, such as .scss=>.css
 *
 * @param $source string The input string to replace.
 * @return string The replaced string, or original string if no replacement
 * is necessary.
 */
public static function renameSource($source) {
	foreach (self::$sourceMap as $match => $replace) {
		if(preg_match($match, $source)) {
			return preg_replace($match, $replace, $source);
		}
	}

	return $source;
}
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

			// Recursive directory requirement.
			FileSystem::loopDir($path, $contents,
			function($item, $iterator, &$contents) {
				if($item->isDir()) {
					return;
				}
				if($item->getExtension() == "js") {
					$contents .= ClientSideCompiler::process_js(
						$item->getPathname(), true) . "\n";
				}
			});
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

private static function process_sass($sourcePath) {
	$sass = new Sass($sourcePath);
	return $sass->parse();	
}

private static function process_scss($sourcePath) {
	return self::process_sass($sourcePath);
}

}#