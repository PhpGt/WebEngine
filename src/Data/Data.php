<?php
/**
 * A factory for getting reference to a Data Source.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Data;

class Data {

const SOURCE_CSV = "Csv";
const SOURCE_SQL = "Sql";
const SOURCE_GOOGLESHEET = "GoogleSheet";

/**
 * Gets an instance of a Data Source of the given type using the provided
 * connection parameters.
 *
 * @param string $type
 * @param array|null $connectionParams
 *
 * @return Container
 */
public function get($type, $connectionParams = null) {
	$baseNS = "\\Gt\\Data\\Source\\";
	$typeNS = "$type\\{$type}Source";
	$fullNS = $baseNS . $typeNS;
	$sourceObj = new $fullNS($connectionParams);

	return $sourceObj;
}

}#