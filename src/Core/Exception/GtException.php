<?php
/**
 * Base exception for use within applications.
 *
 * TODO: Depending on application debug configuration, will display 500 error
 * and stack trace, or generic error page.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Core\Exception;

class GtException extends \Exception {}#