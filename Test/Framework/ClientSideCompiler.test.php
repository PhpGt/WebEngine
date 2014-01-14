<?php class ClientSideCompilerTest extends PHPUnit_Framework_TestCase {

public function setup() {
	removeTestApp();
	createTestApp();
	require_once(GTROOT . "/Framework/ClientSideCompiler.php");
}

public function tearDown() {
	removeTestApp();
}

private function putApprootFileContents($fileArray /*, [$fileArrayN ..] */ ) {
	$fileArrayArray = func_get_args();
	foreach ($fileArrayArray as $fileArray) {
		if(array_keys($fileArray) !== range(0, count($fileArray) - 1)) {
			// Array is associative.
			foreach ($fileArray as $file => $content) {
				$filePath = APPROOT . $file;
				if(!is_dir(dirname($filePath))) {
					mkdir(dirname($filePath), 0775, true);
				}
				file_put_contents($filePath, $content);
			}
		}
		else {
			// Array is not associative.
			foreach ($fileArray as $file) {
				$filePath = APPROOT . $file;
				if(!is_dir(dirname($filePath))) {
					mkdir(dirname($filePath), 0775, true);
				}
				touch($filePath);
			}
		}
	}		
}

/**
 * Shorthand function that creates physical source files within APPROOT, fills
 * with the given contents, and also runs assertions of expected contents
 * against the processed files' contents.
 */
private function makeAssetAssertions($approotFileAssertionArray) {
	foreach ($approotFileAssertionArray as $approotFile => $contentsArray) {
		$sourceContents = is_array($contentsArray)
			? $contentsArray[0]
			: $contentsArray;

		$this->putApprootFileContents([$approotFile => $sourceContents]);

		if(is_array($contentsArray)) {
			$processed = ClientSideCompiler::process(APPROOT . $approotFile);

			// Allow testing arrays and regexes.
			if(is_array($contentsArray[1])) {
				foreach ($contentsArray[1] as $contentToCheck) {
					$this->assertContains($contentToCheck, $processed);
				}
			}
			else if(preg_match("/^\/.*\/[a-z]*$/", $contentsArray[1])) {
				$this->assertRegExp($contentsArray[1], $processed);
			}
			else {
				$this->assertContains($contentsArray[1], $processed);
			}
		}
	}
}

/**
 * The process function on the ClientSideCompiler calls a process function
 * according to  the file extension. Scss should use the SassParser class.
 */
public function testProcessScss() {
	$this->makeAssetAssertions([
		"/Style/Main.scss" => [
			"\$red: rgba(red, 0.8);
			body {
				> h1 {
					color: \$red;
				}
			}", "body > h1 {"],

		"/Style/InnerDir/IncludeMe.scss" => [
			"a#special {
				background: lighten(blue, 1.5);
			}", "a#special"],

		"/Style/Includer.scss" =>
			"@import InnerDir/IncludeMe;
			div.container {
				background: blue;
			}",
	]);
}

/**
 * The process function on the ClientSideCompiler calls a process function
 * according to  the file extension. Js should use the JsParser class.
 */
public function testProcessJs() {
	$this->makeAssetAssertions([
		"/Script/Inc/IncludeMe.js" => 
			"msg += 'Included from IncludeMe';",
		"/Script/Inc/SubDir/InnerInclude.js" =>
			"msg += 'Included from the deeper directory';",
		"/Script/Inc/SubDir/InnerInclude2.js" =>
			"msg += 'Another deep include';",
		"/Script/Main.js" => [
			"var msg = 'Initialised message.';
			// Going to require them all individually,
			// the first done relatively...
			//= require Inc/IncludeMe.js
			//= require /Script/Inc/SubDir/InnerInclude.js
			//= require /Script/Inc/SubDir/InnerInclude2.js"
			, [
				"Initialised message.",
				"Included from IncludeMe",
				"Included from the deeper directory",
				"Another deep include",
			]],
	]);

	// Same test, but with require_tree this time.
	removeTestApp();
	createTestApp();
	$this->makeAssetAssertions([
		"/Script/Inc/IncludeMe.js" => 
			"msg += 'Included from IncludeMe';",
		"/Script/Inc/SubDir/InnerInclude.js" =>
			"msg += 'Included from the deeper directory';",
		"/Script/Inc/SubDir/InnerInclude2.js" =>
			"msg += 'Another deep include';",
		"/Script/Main.js" => [
			"var msg = 'Initialised message.';
			// Going to require them all individually,
			// the first done relatively...
			//= require_tree /Script/Inc"
			, [
				"Initialised message.",
				"Included from IncludeMe",
				"Included from the deeper directory",
				"Another deep include",
			]],
	]);

	// Same test again, but with require_tree being relative this time.
	removeTestApp();
	createTestApp();
	$this->makeAssetAssertions([
		"/Script/Inc/IncludeMe.js" => 
			"msg += 'Included from IncludeMe';",
		"/Script/Inc/SubDir/InnerInclude.js" =>
			"msg += 'Included from the deeper directory';",
		"/Script/Inc/SubDir/InnerInclude2.js" =>
			"msg += 'Another deep include';",
		"/Script/Main.js" => [
			"var msg = 'Initialised message.';
			// Going to require them all individually,
			// the first done relatively...
			//= require_tree Inc"
			, [
				"Initialised message.",
				"Included from IncludeMe",
				"Included from the deeper directory",
				"Another deep include",
			]],
	]);
}

/**
 * Given an array of preprocessed css files and an output filename, the
 * ClientSideCompiler should minify the css into a single file and remove the
 * preprocessed files from the public webroot.
 */
public function testMinifyCss() {
	
}

/**
 * Given an array of preprocessed js files and an output filename, the 
 * ClientSideCompiler should minify the js into a single file and remove the
 * preprocessed files from the public webroot.
 */
public function testMinifyJs() {
	$js = "";

	$jsFileArray = [
		GTROOT  . "/Script/Gt.js" => null,
		APPROOT . "/Script/Main.js" => ';go(function() {
		var one,
			two,
			three,

		e_click_button = function(e) {
			e.preventDefault();
			var onePlusTwoPlusThree = one + two + three;

			alert("One + two + three = " + onePlusTwoPlusThree);
		};

		dom("button#btn_clicker").addEventListener(
			"click", e_click_button);
		});'
	];

	$fileArray = array();

	foreach ($jsFileArray as $file => $contents) {
		if(!is_null($contents)) {
			if(!is_dir(dirname($file))) {
				mkdir(dirname($file), 0775, true);
			}
			file_put_contents($file, $contents);
		}
		$fileArray[] = $file;
	}

	Minifier::minify($fileArray);
}

/**
 * The ClientSideCompiler should provide a map between source filenames and 
 * destination filenames (after processing), according to the file extension.
 * For example, /Style/Main.scss will want to be mapped to /Style/Main.css
 * before it is rendered in the DOM head.
 */
public function testSourceMap() {

}

}#