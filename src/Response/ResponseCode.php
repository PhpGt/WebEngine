<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Response;

class ResponseCode {

const TYPE_INFORMATIONAL	= 1;
const TYPE_SUCCESS			= 2;
const TYPE_REDIRECTION		= 3;
const TYPE_CLIENT_ERROR		= 4;
const TYPE_SERVER_ERROR		= 5;

const REDIRECT_PERMANENT	= 301;
const REDIRECT_FOUND		= 302;
const REDIRECT_TEMPORARY	= 302;
const REDIRECT_SEE_OTHER	= 303;

private $typeNameArray = [
	1 => "Informational",
	2 => "Success",
	3 => "Redirection",
	4 => "Client Error",
	5 => "Server Error",
];

private static $descriptionArray = [
	// TYPE_INFORMATIONAL
	100 => "Continue",
	101 => "Switching Protocols",
	102 => "Processing",

	// TYPE_SUCCESS
	200 => "OK",
	201 => "Created",
	202 => "Accepted",
	203 => "Non-Authoritative Information",
	204 => "No Content",
	205 => "Reset Content",
	206 => "Partial Content",
	207 => "Multi-Status",
	208 => "Already Reported",
	226 => "IM Used",

	// TYPE_REDIRECTION
	300 => "Multiple Choices",
	301 => "Moved Permanently",
	302 => "Found",
	303 => "See Other",
	304 => "Not Modified",
	305 => "Use Proxy",
	306 => "Switch Proxy",
	307 => "Temporary Redirect",
	308 => "Permanent Redirect",

	// TYPE_CLIENT_ERROR
	400 => "Bad Request",
	401 => "Unauthorized",
	402 => "Payment Required",
	403 => "Forbidden",
	404 => "Not Found",
	405 => "Method Not Allowed",
	406 => "Not Acceptable",
	407 => "Proxy Authentication Required",
	408 => "Request Timeout",
	409 => "Conflict",
	410 => "Gone",
	411 => "Length Required",
	412 => "Precondition Failed",
	413 => "Request Entity Too Large",
	414 => "Request-URI Too Long",
	415 => "Unsupported Media Type",
	416 => "Requested Range Not Satisfiable",
	417 => "Expectation Failed",
	418 => "I'm a teapot",
	419 => "Authentication Timeout",

	// TYPE_SERVER_ERROR
	500 => "Internal Server Error",
	501 => "Not Implemented",
	502 => "Bad Gateway",
	503 => "Service Unavailable",
	504 => "Gateway Timeout",
	505 => "HTTP Version Not Supported",
	506 => "Variant Also Negotiates",
	507 => "Insufficient Storage",
	508 => "Loop Detected",
	509 => "Bandwidth Limit Exceeded",
	510 => "Not Extended",
	511 => "Network Authentication Required",
];

/**
 * Gets the status code associated with the given name.
 *
 * @param string $name Text description of status code
 *
 * @return int Status code for provided name
 */
public static function getByName($name) {
	$normalisedName = strtolower($name);
	$normalisedName = str_replace(" ", "", $normalisedName);

	foreach (self::$descriptionArray as $code => $description) {
		$normalisedDescription = strtolower($description);
		$normalisedDescription = str_replace(" ", "", $normalisedDescription);

		if($normalisedName === $normalisedDescription) {
			return (int)$code;
		}
	}
}

/**
 * @param int $code Current response code to set using Headers object
 */
public function __construct($code = 200) {
	Headers::code($code);
}

/**
 * @return string The text description for the current HTTP response code
 */
public function getDescription() {
	return self::$descriptionArray[Headers::code()];
}

/**
 * @return array The list of all possible codes and their descriptions in an
 * indexed array
 */
public function getAllCodesAndDescriptions() {
	return self::$descriptionArray;
}

/**
 * @return string The type name of the current response code
 */
public function getTypeName() {
	$firstDigit = $this->getType();
	return $this->typeNameArray[$firstDigit];
}

/**
 * @return int The type-code associated to the current response code's type
 */
public function getType() {
	// Grab the first digit of the code, negative check required for
	// dash character.
	return (int)substr(Headers::code(), 0, Headers::code() < 0 ? 2 : 1);
}

/**
 * Sends the current HTTP Status Code to the browser.
 */
public function send() {
	http_response_code(Headers::code());
}

public function set($code) {
	return Headers::code($code);
}

public function get() {
	return Headers::code();
}

}#