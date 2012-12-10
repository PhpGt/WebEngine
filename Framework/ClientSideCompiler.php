<?php class ClientSideCompiler {
/**
* When client side compilation is enabled, the Injector object manipulates
* the DOM's head element by removing all link and script elements and replacing
* them with the compiled/minified versions.
* TODO: Implement these steps.
* Steps made in this file:
* 1) Loop over all files within Style and Script directories of APPROOT and
* GTROOT, making a list of all files to be copied.
* 2) For each .js and .css file, check the filemtime against the public
* files of the same name, or the compiled Script.js/Style.css file. Remove file
* from copy list if not changed.
* 3) For each .scss file, check the filemtime against the public files of
* the same name, with .css extension, or the compiled Style.css file, and
* pre-process if necessary. Remove file from copy list if not changed.
* 4) If there are files in the copy list (there is a change), empty the public
* www directory and either copy the files or create a compiled file.
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

private function sassParse($filePath) {
	$sassParser = new SassParser_Utility($filePath);
	$parsedString = $sassParser->parse();

	return file_put_contents($filePath . ".css", $parsedString) >= 0;
}

/**
* Only performed if the isClientCompiled setting is enabled. Takes all link
* elements without 'media' or 'nocompile' attributes and removes them from the
* DOM, then replaces them with a single minified link element. 
*/
private function injectStyleSheets($dom) {
	// Find all stylesheets except those
	// that are required to be loaded separately.
	$styleLinkArray = 
	$dom["link[rel='stylesheet'][not(@media)][not(@nocompile)]"];
	$styleString = "";
	$styleCompileFile = APPROOT . DS . "www" . DS . "Style.css";
	$styleCompileFileModified = 0;
	if(file_exists($styleCompileFile)) {
		$styleCompileFileModified = filemtime($styleCompileFile);
	}
	$styleCacheInvalid = false;

	// Compile them into a single string.
	foreach($styleLinkArray as $styleLink) {
		$filePath = $styleLink->getAttribute("href");
		$href = $filePath;
		// Add directory to filepath if requested.
		if($filePath[0] != "/") {
			$filePath = DS . DIR . DS . $filePath;
		}
		while(strstr($filePath, "//")) {
			$filePath = str_replace("//", "/", $filePath);
		}
		$filePath = str_replace("/", DS, $filePath);

		// Attempt to find the file in the application's directory with a
		// framework fallback.
		$pathArray = array(
			APPROOT . DS . "Style" . $filePath,
			GTROOT . DS . "Style" . $filePath
		);

		$foundStyle = false;
		foreach ($pathArray as $path) {
			if(file_exists($path)) {
				$foundStyle = true;
				$fileMtime = filemtime($path);
				if($fileMtime > $styleCompileFileModified) {
					$styleCacheInvalid = true;
				}

				$styleString .= "/**********************" . "\n";
				$styleString .= " * " . $href             . "\n";
				$styleString .= " *********************/" . "\n" . "\n";
				$styleString .= file_get_contents($path)  . "\n" . "\n";

				break;
			}
		}
		
		if(!$foundStyle) {
			$styleString .= "/**********************"       . "\n";
			$styleString .= " * CANNOT FIND FILE: " . $href . "\n";
			$styleString .= " *********************/"       . "\n" . "\n";
		}
	}

	// Only write the compiled file if there are newer CSS files.
	if($styleCacheInvalid) {
		file_put_contents($styleCompileFile, $styleString);
	}

	$styleCompileFileUrl = str_replace(
	APPROOT . DS . "www", "", $styleCompileFile);

	// Remove the link elements from the page, replace them with the cache.
	$styleLinkArray->remove();

	// ALPHATODO:
	// TODO: This is a hack until the DOM's prepend, before, after functions
	// have been written and tested.
	// Simply moves any <link> elements with a "media" attribute to the end
	// of the head, after the compiled file is appended.
	$existingLinks = $dom["head link"];

	$dom["head"]->append(
	$dom->create("link", array(
		"rel"    => "stylesheet",
		"href"   => $styleCompileFileUrl
		))
	);

	$existingLinks->remove();
	$dom["head"]->append($existingLinks);
}

/**
* Only performed if the isClientCompiled setting is enabled. Takes all script
* elements without 'nocompile' attributes and removes them from the DOM,
* then replaces them with a single script element containing a compiled version. 
*/
private function injectJavaScript($dom) {
	// Find all scripts.
	$scriptArray = $dom["script[@src][not(@nocompile)]"];
	$scriptString = "";
	$scriptCompileFile = APPROOT . DS . "www" . DS . "Script.js";

	$scriptCompileFileModified = 0;

	if(file_exists($scriptCompileFile)) {
		$scriptCompileFileModified = filemtime($scriptCompileFile);
	}
	$scriptCacheInvalid = false;

	// Compile them into a single string.
	foreach($scriptArray as $script) {
		$filePath = $script->getAttribute("src");
		$src = $filePath;
		// Add directory to filepath if requested.
		if($filePath[0] != "/") {
			$filePath = DS . DIR . DS . $filePath;
		}
		while(strstr($filePath, "//")) {
			$filePath = str_replace("//", "/", $filePath);
		}
		$filePath = str_replace("/", DS, $filePath);

		// Attempt to find the file in the application's directory with a
		// framework fallback.
		$pathArray = array(
			APPROOT . DS . "Script" . $filePath,
			GTROOT . DS . "Script" . $filePath
		);

		$foundScript = false;
		foreach ($pathArray as $path) {
			if(file_exists($path)) {
				$foundScript = true;
				$fileMtime = filemtime($path);
				if($fileMtime > $scriptCompileFileModified) {
					$scriptCacheInvalid = true;
				}

				$scriptString .= "/**********************" . "\n";
				$scriptString .= " * " . $src              . "\n";
				$scriptString .= " *********************/" . "\n" . "\n";
				$scriptString .= file_get_contents($path)  . "\n" . "\n";

				break;
			}
		}
		if(!$foundScript) {
			$scriptString .= "/**********************"      . "\n";
			$scriptString .= " * CANNOT FIND FILE: " . $src . "\n";
			$scriptString .= " *********************/"      . "\n" . "\n";
		}
	}

	// Only write the compiled file if there are newer CSS files.
	if($scriptCacheInvalid) {
		$compiler = new JavaScriptCompiler_Utility($scriptString);
		$compiledString = trim($compiler->output());
		if(!empty($compiledString) ) {
			$scriptString = $compiledString;
		}

		file_put_contents($scriptCompileFile, $scriptString);
	}

	$scriptCompileFileUrl = str_replace(
	APPROOT . DS . "www", "", $scriptCompileFile);

	// Remove the link elements from the page, replace them with the cache.
	$scriptArray->remove();
	$dom["head"]->append(
		$dom->create("script", array(
			"src"   => $scriptCompileFileUrl
		))
	);
}

}?>