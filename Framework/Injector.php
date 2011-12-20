<?php
class Injector {
	/**
	* TODO: Docs.
	*/
	public function __construct($dom) {
		$this->injectStyleSheets($dom);
		// TODO: Second param indicates whether to compile - add ability to
		// change to false for debugging purposes.
		$this->injectJavaScript($dom, true);
	}

	/**
	* TODO: Docs.
	*/
	public function injectStyleSheets($dom) {
		// Find all stylesheets except those
		// that are required to be loaded separately.
		$styleLinkArray = 
		$dom["link[rel='stylesheet'][not(@media)][not(@nocompile)]"];
		$styleString = "";
		$styleCompileFile = APPROOT . DS . "Web" . DS . "Style.css";
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
		APPROOT . DS . "Web", "", $styleCompileFile);

		// Remove the link elements from the page, replace them with the cache.
		$styleLinkArray->remove();

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
	* TODO: Docs.
	*/
	public function injectJavaScript($dom, $compile) {
		// Find all scripts.
		$scriptArray = $dom["script[@src][not(@nocompile)]"];
		$scriptString = "";
		$scriptCompileFile = APPROOT . DS . "Web" . DS . "Script.js";

		$scriptCompileFileModified = 0;

		if(file_exists($scriptCompileFile)) {
			$scriptCompileFileModified = filemtime($scriptCompileFile);
		}
		$scriptCacheInvalid = false;

		//var_dump($scriptCompileFileModified);die();

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
			if($compile) {
				$compiler = new JavaScriptCompiler_Utility($scriptString);
				$scriptString = $compiler->output();
			}
			file_put_contents($scriptCompileFile, $scriptString);
		}

		$scriptCompileFileUrl = str_replace(
		APPROOT . DS . "Web", "", $scriptCompileFile);

		// Remove the link elements from the page, replace them with the cache.
		$scriptArray->remove();
		$dom["head"]->append(
			$dom->create("script", array(
				"src"   => $scriptCompileFileUrl
			))
		);
	}
}
?>