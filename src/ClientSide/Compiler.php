<?php
/**
 * Provides a standard interface for compiling client-side scripts such as
 * pre-processed Style Sheets or JavaScript.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Response\Response;
use \scssc as ScssParser;

class Compiler {

/**
 * Parses a source file and returns its compiled content, or the original
 * content if no compilation is necessary/possible.
 *
 * @param string $inputFile Absolute path of input file
 *
 * @return string Content of file to write to public directory
 */
public static function parse($inputFile) {
	$ext = strtolower(pathinfo($inputFile, PATHINFO_EXTENSION));

	$content = file_get_contents($inputFile);

	switch ($ext) {
	case "scss":
		$scss = new ScssParser();
		$scss->addImportPath(Path::get(Path::STYLE));
		$scss->addImportPath(dirname($inputFile));

		try {
			$content = $scss->compile($content);
		}
		catch(\Exception $e) {
			$msg = $e->getMessage();
			if(strpos($msg, "parse error") < 1) {
				throw new CompilerParseException("SCSS $msg");
			}
		}
		break;

	default:
		break;
	}

	return $content;
}

}#