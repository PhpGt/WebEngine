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
private $_compileFunctionList = array(
	"js" => "javaScript",
);

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
		$elementArray = array();
		if(!is_null($domHead)) {
			$elementArray = $domHead[$tagName];		
		}

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
				continue;
			}

			$fileContents = file_get_contents("$wwwDir/$source");
			file_put_contents("$wwwDir/{$tagDetails["combinedFile"]}", 
				$fileContents . "\n", FILE_APPEND);

			unlink("$wwwDir/$source");
		}

		if(!is_null($domHead)) {
			$elementArray->remove();
			$newElement = new DomEl($domHead->_dom, $tagName);
			$newElement->setAttribute(
				$tagDetails["sourceAttribute"], 
				"/" . $tagDetails["combinedFile"]
			);
			$domHead->appendChild($newElement);			
		}
	}
}

public function compile() {
	$wwwDir = APPROOT . "/www";
	$fileNameArray = array(
		"Script.js",
		"Style.css",
	);

	foreach ($fileNameArray as $fileName) {
		$filePath = "$wwwDir/$fileName";
		$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
		if(!file_exists($filePath)) {
			continue;
		}

		if(!array_key_exists($extension, $this->_compileFunctionList)) {
			continue;
		}

		$compiledString = call_user_func_array(
			[$this, $this->_compileFunctionList[$extension]],
			[$filePath]
		);

		file_put_contents($filePath, $compiledString);
	}
}

private function javaScript($path) {
	$js = file_get_contents($path);
	if(strlen(trim($js)) === 0) {
		return $js;
	}

	$http = new Http();
	$http->setOption("timeout", 10);
	$http->setHeader("Content-Type: application/x-www-form-urlencoded");
	// Google Closure service does not support multipart/form-data POST request,
	// which is very odd. Instead, data has to be in the query string.
	$response = $http->execute(
		// "http://g105b.com/PostTest.php",
		"http://closure-compiler.appspot.com/compile"
			. "?output_info=compiled_code"
			. "&output_format=text"
			. "&compilation_level=SIMPLE_OPTIMIZATIONS"
			. "&js_code=" . urlencode($js),
		"POST"
	);

	$js_c = $response["body"];

	if(!empty($js_c)) {
		return $js_c;
	}

	return $js;
}

}#