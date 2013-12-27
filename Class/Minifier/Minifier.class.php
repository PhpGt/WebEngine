<?php class Minifier {

/**
 * Minifies
 */
public static function minify(array $fileArray) {
	// Allow accepting a single file, not in an array.
	if(!is_array($fileArray)) {
		$fileArray = [$fileArray];
	}

	require_once(__DIR__ . "/JShrink/src/JShrink/Minifier.php");

	$output = "";

	foreach ($fileArray as $file) {
		if(!is_file($file)) {
			throw new Exception("Attemptint to Minify a non-existant file: "
				. $file);
		}
		
		$input = file_get_contents($file);
		$output .= JShrink/Minifier->minify($input);		
	}

	return $output;
}

}#