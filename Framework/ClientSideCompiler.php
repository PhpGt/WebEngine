<?php final class ClientSideCompiler {
/**
 * The ClientSideCompiler pre-processes necessary files, converting them into
 * their processed equivalents, removing the originals. If App_Config has the
 * client-side compilation enabled, all resources will be minified and compiled
 * into a single resource.
 *
 * The DOM is updated to only include the necessary files, so the HTML should
 * reference all JS and CSS/SCSS files without the worry of multiple requests
 * or unminified scripts being exposed publicly.
 */
public function __construct() {}

/**
 * Process a client-side file, such as SCSS, and replace the file with its
 * processed couterpart.
 * Only SCSS is supported at this moment.
 */
public function process($filePath) {
	$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
	switch($extension) {
	case "scss":
		$filePathProcessed = preg_replace("/\.scss$/i", ".css", $filePath);

		$sass = new Sass($filePath);
		$parsedString = $sass->parse();
		if(file_put_contents($filePathProcessed, $parsedString) === false) {
			return false;
		} 
		unlink($filePath);
		return true;

		break;
	default:
		break;
	}
}

/**
 * Combines all source files *in order* into a single source file, and placed
 * into the www directory. The order of source files is taken from their order
 * in the DOM head. All source files that don't exist in the DOM head will be
 * kept in their original place, for you to combine yourself.
 *
 * All source files will be removed after combining.
 */
public function combine($domHead) {
	$wwwDir = APPROOT . "/www";
	$tagNameArray = array(
		"script" => [
			"sourceAttribute" => "src",
			"requiredAttributes" => [],
			"combinedFile" => "Script.js",
		],
		"link" => [
			"sourceAttribute" => "href",
			"requiredAttributes" => ["rel" => "stylesheet"],
			"combinedFile" => "Style.css",
		],
	);

	foreach ($tagNameArray as $tagName => $tagDetails) {
		$elementArray = $domHead[$tagName];

		foreach ($elementArray as $element) {
			if(!$element->hasAttribute($tagDetails["sourceAttribute"])) {
				continue;
			}
			foreach ($element["requiredAttributes"] as $requiredAttribute) {
				if(!$element->hasAttribute($requiredAttribute)) {
					continue;
				}
			}

			$source = $element->getAttribute($tagDetails["sourceAttribute"]);
			if(!file_exists("$wwwDir/$source")) {
				// TODO: Handle missing file.
				continue;
			}

			$fileContents = file_get_contents("$wwwDir/$source");
			file_put_contents("$wwwDir/{$tagDetails["combinedFile"]}", 
				$fileContents . "\n", FILE_APPEND);

			unlink("$wwwDir/$source");
		}

		$elementArray->remove();
		$newElement = new DomEl($domHead->_dom, $tagName);
		$newElement->setAttribute(
			$tagDetails["sourceAttribute"], 
			"/" . $tagDetails["combinedFile"]
		);
		$domHead->appendChild($newElement);
	}
}

public function compile($filePath) {

}

}#