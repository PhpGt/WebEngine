<?php
/**
 *
 *
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\ThirdParty;

use \SplFileObject as File;
use \SplTempFileObject as Temp;

class Csv {

private $file;

public function __construct($filePath) {
	$this->file = new File($filePath);
	$this->file->setFlags(
		File::READ_CSV |
		File::READ_AHEAD |
		File::SKIP_EMPTY |
		File::DROP_NEW_LINE
	);
}

// So many CSV-related functions and settings built right in!
// http://php.net/manual/en/class.splfileobject.php

}#