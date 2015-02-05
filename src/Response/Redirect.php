<?php
/**
 * Used to perform temporary or permanent HTTP redirects within
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Response;

class Redirect {

private $uri;
private $code;

/**
 * @param string $uri The URI to redirect to.
 * @param int $code Optional. The HTTP response code to send. Defaults to
 * 303 See Other. Passing in 301 will permanently redirect the requested URI
 * with the provided URI, passwing 302 will temporarily redirect.
 * @param bool $respond Whether to send headers automatically.
 */
public function __construct($uri, $code = 303, $respond = true) {
	// Force an absolute path.
	// First check that another hostname is not provided (indicated with //)
	// Then if the URI does not start with a slash, add one.
	if(!strstr($uri, "//")
	&& strpos($uri, "/") !== 0) {
		$uri = "/$uri";
	}

	$this->uri = $uri;
	$this->code = $code;

	if($respond) {
		$this->sendHeader();
	}
}

/**
 * Sends the Location HTTP header, replacing any previous similar header.
 * If headers have already been sent, an exception is thrown.
 */
public function sendHeader() {
	if(headers_sent($file, $line)) {
		throw new HeadersAlreadySentException("Sent from $file:$line.");
	}

	Headers::code($this->code);
	Headers::add("Location", $this->uri);
	Headers::send();
	exit;
}

}#