<?php
/**
 * 
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

abstract class ResponseContent {

/**
 * Serialises the response in its current state and adds it to the output
 * buffer, ready for flushing at the end of the response cycle.
 */
public function flush() {
	echo $this->__toString();
}

}#