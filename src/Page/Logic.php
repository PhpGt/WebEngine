<?php
/**
 * Alias class to \Gt\Logic\PageLogic. This is here so that the namespace of
 * Applications' PageLogic classes matches the namespace of the extended
 * Gt base class.
 *
 * For example: The page at /dir/nested/page represents the class
 * \AppNS\Page\Logic\Dir\Nested\Page, and would make sense to inherit
 *    \Gt\Page\Logic rather than \Gt\Logic\PageLogic
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Page;

abstract class Logic extends \Gt\Logic\PageLogic {}