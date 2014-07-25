<?php
/**
 * A property-accessible object representation of a configuration ini block.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;

/**
 * @property-read string $api_prefix
 * @property-read string $api_default_type
 * @property-read bool $pageview_html_extension
 * @property-read bool $pageview_trailing_directory_slash
 * @property-read string $index_filename
 * @property-read bool $index_force
 */
class ConfigObj extends Obj {}#