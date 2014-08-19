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
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Page;

abstract class Logic extends \Gt\Logic\PageLogic {}