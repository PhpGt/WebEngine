<?php
/**
 * Used to perform temporary or permanent HTTP redirects within 
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Response;

class Redirect {

/**
 * @param string $uri The URI to redirect to.
 * @param int $code Optional. The HTTP response code to send. Defaults to
 * 303 See Other. Passing in 301 will permanently redirect the requested URI
 * with the provided URI, passwing 302 will temporarily redirect.
 */
public function __construct($uri, $code = 303) {
	error_log($uri);
	header("Location: $uri", true, $code);
	exit;
}

}#