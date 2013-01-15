<?php class ClientSideCompiler {
/**
 * The ClientSideCompiler pre-processes all SCSS files, converting them into
 * their CSS equivalents, and optionally minifies and compiles all CSS and JS
 * files into a single Script.min.js and Style.min.css
 * The DOM is updated to only include the necessary files, so the HTML should
 * reference all JS and CSS/SCSS files without the worry of multiple requests
 * or unminified scripts being exposed publicly.
 */
public function __construct($dom, $isCompiled) {
	// Ensure all .scss files are pre-processed before anything else.
	$this->preprocess($dom);

	if($isCompiled) {
		$this->compileStyleSheets($dom);
		$this->compileJavaScript($dom);
	}
}

/**
 * Pre-processes any .scss stylesheets into .css files, ready for optional
 * compilation, and handling by the FileOrganiser class.
 * After running this function, all .scss files are converted into .scss.css
 * representations of the original, preserving white-space and comments.
 */
private function preprocess($dom) {
	$styleLinkArray = $dom["head > link[rel='stylesheet']"];
	foreach($styleLinkArray as $styleLink) {
		// Only care about .scss files.
		if(!preg_match("/\.scss$/i", $styleLink->href)) {
			continue;
		}
		$pathArray = array(
			APPROOT . DS . "Style" . DS,
			GTROOT . DS . "Style" . DS
		);
		$filePath = str_replace("/", DS, $styleLink->href);
		foreach($pathArray as $path) {
			if(!file_exists($path . $filePath)) {
				continue;
			}
			if(!$this->sassParse($path . $filePath)) {
				die("Error parsing SASS file.");
			}
			$styleLink->href .= ".css";
			break;
		}
	}
}

// TODO: Issue #62 and #64 - Don't preprocess the sass files to the Style
// direcotry. Instead, output to the www directory... but two extra things must
// be done to achieve this: the compiler must work within the www directory,
// and the FileOrganiser must ignore .scss files. 
private function sassParse($filePath) {
	$sassParser = new SassParser_Utility($filePath);
	$parsedString = $sassParser->parse();

	return file_put_contents($filePath . ".css", $parsedString) >= 0;
}

/**
 * Takes all <link rel="stylesheet"> elements in the html head and minifies them
 * into one file, named Style.min.css.
 * @param  Dom $dom The current DOM.
 * @return int      The number of stylesheets minified.
 */
private function compileStyleSheets($dom) {
	$css = "";
	$lastModify = 0;
	$styleList = $dom["head > link[rel='stylesheet']"];
	foreach($styleList as $style) {
		$pubUri = $style->href;
		$filePathArray = array(
			APPROOT . "/Style/" . $pubUri,
			GTROOT  . "/Style/" . $pubUri,
		);

		foreach($filePathArray as $filePath) {
			if(!file_exists($filePath)) {
				continue;
			}

			// Check to see if file is .scss, so the actual preprocessed CSS can
			// be used instead.
			// $scssEnd = ".scss";
			// if(substr_compare(
			// $filePath, 
			// $scssEnd, 
			// strlen($scssEnd) - strlen($scssEnd), 
			// strlen($scssEnd)) === 0) {
			// 	$filePath .= ".css";
			// }
			$css .= file_get_contents($filePath);
			$fmtime = filemtime($filePath);
			if($fmtime > $lastModify) {
				$lastModify = $fmtime;
			}

			break;
		}
	}

	$pubMinUri = "/Style.min.css";

	// Ensure that compilation only occurs when there are modified styles.
	if(file_exists(APPROOT . "/Script" . $pubMinUri)) {
		if(filemtime(APPROOT . "/Script" . $pubMinUri) >= $lastModify) {
			return 0;
		}
	}

	$cssCompiler = new StyleSheetCompiler_Utility($css);
	$css = $cssCompiler->output();

	if(false === file_put_contents(APPROOT . "/Style" . $pubMinUri, $css)) {
		return false;
	}

	$styleList->remove();
	$styleNew = $dom->create("link", ["href" => $pubMinUri, 
		"rel" => "stylesheet"]);
	$dom["head"]->append($styleNew);

	return $styleList->length;
}

/**
 * Takes all <script> elements in the html head and compiles and minifies them
 * into one file, named Script.min.js.
 * @param  Dom $dom The current DOM
 * @return int      The number of scripts compiled.
 */
private function compileJavaScript($dom) {
	$js = "";
	$lastModify = 0;
	$scriptList = $dom["head > script"];
	foreach($scriptList as $script) {
		if(!$script->hasAttribute("src")) {
			$scriptList->removeElement($script);
			continue;
		}
		$pubUri = $script->src;
		$filePathArray = array(
			APPROOT . "/Script/" . $pubUri,
			GTROOT  . "/Script/" . $pubUri,
		);

		foreach($filePathArray as $filePath) {
			if(!file_exists($filePath)) {
				continue;
			}
			$js .= file_get_contents($filePath);
			$fmtime = filemtime($filePath);
			if($fmtime > $lastModify) {
				$lastModify = $fmtime;
			}

			break;
		}
	}

	$pubMinUri = "/Script.min.js";

	$scriptList->remove();
	$scriptNew = $dom->create("script", ["src" => $pubMinUri]);
	$dom["head"]->append($scriptNew);

	// Ensure that compilation only occurs when there are modified scripts.
	if(filemtime(APPROOT . "/Script" . $pubMinUri) >= $lastModify) {
		return 0;
	}

	$jsCompiler = new JavaScriptCompiler_Utility($js);
	$js = $jsCompiler->output();

	if(false === file_put_contents(APPROOT . "/Script" . $pubMinUri, $js)) {
		return false;
	}

	return $scriptList->length;
}

}?>