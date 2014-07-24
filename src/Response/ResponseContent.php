<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

abstract class ResponseContent implements \Serializable {

/**
 * Serialises the response in its current state and adds it to the output
 * buffer, ready for flushing at the end of the response cycle.
 */
abstract function serialize();

/**
 * Loads a string representation of the current ResponseContent. Page requests
 * will load raw HTML for the Dom Document, API requests will load a PHP
 * object serialized string.
 */
abstract function unserialize($serialized);

}#