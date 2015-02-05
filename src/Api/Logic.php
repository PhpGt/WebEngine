<?php
/**
 * Alias class to \Gt\Logic\ApiLogic. This is here so that the namespace of
 * Applications' ApiLogic classes matches the namespace of the extended
 * Gt base class.
 *
 * For example: The page at /dir/nested/endpoint represents the class
 * \AppNS\Api\Dir\Nested\Endpoint, and would make sense to inherit
 *    \Gt\Api\Logic rather than \Gt\Logic\ApiLogic
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Api;

abstract class Logic extends \Gt\Logic\ApiLogic {}