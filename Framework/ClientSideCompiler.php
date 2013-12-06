<?php final class ClientSideCompiler {
/**
 * The ClientSideCompiler minifies/obfuscates source files.
 */

private static $_processMatches = array(
	"/\.scss$/" => ".css",
);

/**
 * Perform the processing of files that require server-side processing. This
 * does not include minification/obfuscation, only the processing/expansion
 * of files such as scss or JavaScript files with server-side includes.
 *
 * Returns an array with two keys: Destination (including possibly-changed
 * file extension) and Contents - the file contents to write.
 */
public static function process($sourcePath, $destination) {
	if(!file_exists($sourcePath)) {
		throw new Exception("Attempt to process missing file: $sourcePath");
	}

	$ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
	$ext = trim(strtolower($ext));
	$processMethod = "process_$ext";

	if(method_exists("ClientSideCompiler", $processMethod)) {
		return ClientSideCompiler::$processMethod($sourcePath, $destination);
	}

	// Some files may not need processing:
	$contents = file_get_contents($sourcePath);

	if(is_null($destination)) {
		return $contents;
	}

	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

/**
 * Recursive function. When null is passed as destination, will return just the
 * text content of the required JavaScript, including any sub-requires.
 */
private static function process_js($sourcePath, $destination) {
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

				$contents .= self::process_js("$path/$f", null) . "\n";
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

			$contents .= self::process_js($path, null) . "\n";
		}
		else {
			$contents .= $line;
		}
	}
	fclose($fh);

	if(is_null($destination)) {
		return $contents;
	}

	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

private static function process_scss($sourcePath, $destination) {
	$sass = new Sass($sourcePath);
	$contents = $sass->parse();

	if(is_null($destination)) {
		return $contents;
	}
	$destination = preg_replace("/\.scss$/", ".css", $destination);
	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

public static function getProcessDestinations($fileList) {
	$result = array();

	foreach ($fileList as $i => $file) {
		$f = array();
		$f["Source"] = $file;

		foreach (self::$_processMatches as $match => $replace) {
			if(preg_match($match, $file)) {
				$f["Destination"] = preg_replace($match, $replace, $file);
			}
			else {
				$f["Destination"] = $file;
			}
		}

		$result[] = $f;
	}

	return $result;
}

}#