<?php
/**
 * Provides a standard interface for compiling client-side scripts such as
 * pre-processed Style Sheets or JavaScript.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\ClientSide;

use \Gt\Core\Path;
use \Gt\Response\Response;
use \Leafo\ScssPhp\Compiler as ScssParser;

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
			throw new CompilerParseException("SCSS $msg");
		}
		break;

	default:
		break;
	}

	return $content;
}

}#
