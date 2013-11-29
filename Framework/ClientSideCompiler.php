<?php final class ClientSideCompiler {
/**
 * The ClientSideCompiler minifies/obfuscates source files.
 */

/**
 * Perform the processing of files that require server-side processing. This
 * does not include minification/obfuscation, only the processing/expansion
 * of files such as scss or JavaScript files with server-side includes.
 *
 * Returns an array with two keys: Destination (including possibly-changed
 * file extension) and Contents - the file contents to write.
 */
public static function process($sourcePath, $destination) {
	// TODO: For now, return original source.
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

	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

private static function process_js($sourcePath, $destination) {
	// TODO: Expand server-side includes.
	$contents = file_get_contents($sourcePath);
	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

private static function process_scss($sourcePath, $destination) {
	// TODO: Actually process the scss, not just removing the 's'.
	$contents = file_get_contents($sourcePath);
	$destination = preg_replace("/\.scss$/", ".css", $destination);
	return [
		"Destination" => $destination,
		"Contents" => $contents,
	];
}

}#