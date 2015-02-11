<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dispatcher;

class ApiDispatcher extends Dispatcher {


/**
 *
 */
public function getBasePath() {

}

/**
 *
 */
public function getPath($uri, &$fixedUri) {
	// TODO...
}

/**
 *
 */
public function loadSource($path, $pathFile) {
	// TODO...
}

/**
 *
 */
public function loadError($path, $pathFile, $errorCode) {
	// TODO...
}

/**
 *
 */
public function createResponseContent($content, $config) {
	$apiObj = new \Gt\Response\StructuredData\Container();

	return $apiObj;
}

}#