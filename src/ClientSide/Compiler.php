<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\ClientSide;

use \Gt\Response\Response;

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

	switch ($ext) {
	case ".compiledfile":
		break;

	default:
		return file_get_contents($inputFile);
		break;
	}
}

}#