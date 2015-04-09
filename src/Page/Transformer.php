<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Page;

use \Michelf\Markdown;

class Transformer {

const TYPE_MARKDOWN	= "md";
const TYPE_HAML		= "haml";

/**
 * @param string $source Plain-text source content, or the path to the source
 * file
 * @param string $type Type of source content, must be one of this class's
 * type constants, or null if parameter 1 is a file path
 *
 * @return string HTML source transformed from provided source content
 */
public static function toHtml($source, $type = null) {
	if(is_null($type)) {
		$fileInfo = new \SplFileInfo($source);
		$type = $fileInfo->getExtension();

		$source = file_get_contents($source);
	}

	$type = strtolower($type);
	$source = self::fixCharacters($source);

	switch($type) {
	case self::TYPE_MARKDOWN:
		$result = Markdown::defaultTransform($source);
		break;

	default:
		throw new SourceNotValidException();
		break;
	}


	return $result;
}

/**
 * Corrects special characters in input string, such as converting straight
 * quotes to opening/closing quotes, double hyphens to em dashes, etc.
 *
 * @param string $input Source input
 * @param bool $htmlEntities Replace with html entities rather than unicode
 * characters
 *
 * @return string Fixed string
 */
public static function fixCharacters($input, $htmlEntities = false) {
	$replaceWith = [
		// Double or triple hyphen, with no surrounding hyphens.
		"/[^-](-{2,3})[^-]/" => "—",
		// Straight quotes followed by word character.
		"/(\")(?=\w)/" => "“",
		// Straight quotes followed by non-word character.
		"/(\")(?=\W)/" => "”",
		// Whitespace followed by straight apostrophe.
		"/(?<=\s)(')/" => "‘",
		// Non-whitespace followed by straight apostrophe.
		"/(?<=\S)(')/" => "’",
		// Exactly three periods, with no surrounding periods.
		"/[^\.](\.{3})[^\.]/" => "…",
	];

	$output = $input;

	foreach ($replaceWith as $pattern => $replacement) {
		$output = preg_replace($pattern, $replacement, $output);
	}

	return $output;
}

}#