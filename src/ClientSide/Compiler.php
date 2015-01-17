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
		$importPaths = [
			Path::get(Path::STYLE),
			dirname($inputFile),
		];
		$scss = new ScssParser();
		$scss->setImportPaths($importPaths);
		$scss->addImportPath(function($path, $scss) use($importPaths) {
			// Get the path of the current file, attempt relative path import.
			$parsedFiles = $scss->getParsedFiles();
			$currentFileImporting = end($parsedFiles);
			$currentFileImporting = rtrim($currentFileImporting, "/");
			$currentPath = pathinfo($currentFileImporting, PATHINFO_DIRNAME);
			$relativePath = "$currentPath/$path";

			if(is_file($path)) {
				return $path;
			}
			if(is_file($relativePath)) {
				return $relativePath;
			}

			return null;
		});

		// Add magic variable $appRoot.
		$content = "\$APPROOT: \"" . Path::get(Path::ROOT)
			. "\";"
			. "\n\n"
			. $content;

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