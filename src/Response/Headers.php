<?php
/**
 * Represents the HTTP response header list. Used to send HTTP headers on the
 * response.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Response;

class Headers {

private static $headerArray = [];
private static $code = 200;

/**
 * Gets or sets the HTTP response code.
 *
 * @param integer|null $code Code to set, or null to get the current code
 *
 * @return integer The current HTTP response code
 */
public static function code($code = null) {
	if(!is_null($code)) {
		self::$code = (int)$code;
	}

	return self::$code;
}
/**
 * Adds the given HTTP header to the list of headers, to be sent with the HTTP
 * response.
 *
 * @param string $field The name of the HTTP header field
 * @param string $value The value of the HTTP header
 *
 * @return string The raw header, as it is sent over HTTP
 */
public static function set($field, $value) {
	$field = ucfirst($field);
	self::$headerArray[$field] = $value;

	return self::getRaw($field, $value);
}

/**
 * Synonym function for set.
 */
public static function add($field, $value) {
	return self::set($field, $value);
}

/**
 * Return the value of the header with the given field name.
 *
 * @param string $field The name of the HTTP header field
 *
 * @return string|null The value of the HTTP header, or null if there is
 * nothing set.
 */
public static function get($field) {
	$field = ucfirst($field);

	if(isset(self::$headerArray[$field])) {
		return self::$headerArray[$field];
	}

	return null;
}

/**
 * Get a list of all headers ready to be sent as an array.
 *
 * @return array Associative array of headers ready to be sent
 */
public static function getAll() {
	return self::$headerArray;
}

/**
 * Sends all the headers currently waiting to be sent.
 *
 * @param ResponseCode|null $responseCode The optional response code object to
 * use when sending the HTTP response status code.
 *
 * @return string The raw HTTP representation of all headers sent
 */
public static function send($responseCode = null) {
	$rawAll = "";
	foreach (self::$headerArray as $field => $value) {
		$raw = self::getRaw($field, $value);
		@header($raw, true, self::code());

		$rawAll .= $raw . PHP_EOL;
	}

	if(!is_null($responseCode)) {
		$responseCode->send();
	}

	return $rawAll;
}

/**
 * From a given field name and value, return the HTTP/1.1 raw header string.
 * Each header field consists of a name followed by a colon (":") and the
 * field value. Field names are case-insensitive.
 * See: http://www.w3.org/Protocols/rfc2616/rfc2616.html
 *
 * @param string $field The name of the HTTP header field
 * @param string $value The value of the HTTP header
 *
 * @return string The raw header, as it is sent over HTTP
 */
public static function getRaw($field, $value) {
	$field = ucfirst($field);
	return "$field: $value";
}

public static function redirect($uri,
$code = ResponseCode::REDIRECT_TEMPORARY) {
	self::set("Location", $uri);
	self::code($code);
	self::send();
}

}#