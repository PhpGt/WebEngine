<?php
/**
 * A property-accessible object representation of a configuration ini block.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core;

/**
 * @property string $api_prefix
 * @property string $api_default_type
 * @property bool $force_extension
 * @property bool $pageview_trailing_directory_slash
 * @property string $index_filename
 * @property bool $index_force
 */
class ConfigObj extends Obj {

private $name;

public function setName($name) {
	$this->name = strtolower($name);
}

public function getName() {
	return $this->name;
}

}#