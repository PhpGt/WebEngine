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
 * @param string $source Plain-text source content
 * @param string $type Type of source content, must be one of this class's
 * type constants
 *
 * @return string HTML source transformed from provided source content
 */
public static function toHtml($source, $type) {
	$type = strtolower($type);

	switch($type) {
	case self::TYPE_MARKDOWN:
		return Markdown::defaultTransform($source);
	}

	throw new SourceNotValidException();
}

}#