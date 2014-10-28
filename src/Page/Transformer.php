<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Page;

use \Michelf\Markdown;

class Transformer {

const TYPE_MARKDOWN	= "md";
const TYPE_HAML		= "haml";

/**
 *
 */
public static function toHtml($source, $type) {
	$type = strtolower($type);

	switch($type) {
	case self::TYPE_MARKDOWN:
		return Markdown::defaultTransform($source);
		break;

	default:
		throw new SourceNotValidException();
		break;
	}
}

}#