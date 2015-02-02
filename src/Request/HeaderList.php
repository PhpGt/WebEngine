<?php
/**
 * Represents the HTTP request header fields by normalising the $_SERVER array's
 * keys that start with "HTTP_". Notice: These headers are sent by the requester
 * and should not be trusted!
 *
 * Can be accessed as an associative array to reference headers in their
 * original format (i.e. $headers["accept-language"]) or as object properties
 * with underscores (i.e. $headers->accept_language).
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Request;

use \Gt\Core\Obj;

/**
 * For more detailed information:
 * https://en.wikipedia.org/wiki/List_of_HTTP_header_fields#Request_Headers
 *
 * @property string $accept Content-Types that are acceptable for the
 * response
 * @property string $accept_charset Character sets that are acceptable
 * @property string $accept_encoding List of acceptable encodings
 * @property string $accept_language List of acceptable human languages
 * for response
 * @property string $accept_datetime Acceptable version in time
 * @property string $authorization Authentication credentials for HTTP
 * authentication
 * @property string $cache_control Used to specify directives that MUST be
 * obeyed by all caching mechanisms along the request/response chain
 * @property string $connection What type of connection the user-agent
 * would prefer
 * @property string $cookie an HTTP cookie previously sent by the server
 * with Set-Cookie
 * @property string $content_length The length of the request body in
 * octets (8-bit bytes)
 * @property string $content_md5 A Base64-encoded binary MD5 sum of the
 * content of the request body
 * @property string $content_type The MIME type of the body of the request
 * (used with POST and PUT requests)
 * @property string $date The date and time that the message was sent (in
 * "HTTP-date" format as defined by RFC 7231)
 * @property string $expect Indicates that particular server behaviors are
 * required by the client
 * @property string $from The email address of the user making the request
 * @property string $host The domain name of the server (for virtual
 * hosting), and the TCP port number on which the server is listening. The port
 * number may be omitted if the port is the standard port for the service
 * requested
 * @property string $if_match Only perform the action if the client
 * supplied entity matches the same entity on the server. This is mainly for
 * methods like PUT to only update a resource if it has not been modified
 * since the user last updated it
 * @property string $if_modified_since Allows a 304 Not Modified to be
 * returned if content is unchanged
 * @property string $if_none_match Allows a 304 Not Modified to be
 * returned if content is unchanged
 * @property string $if_range If the entity is unchanged, send me the
 * part(s) that I am missing; otherwise, send me the entire new entity
 * @property string $if_unmodified_since Only send the response if the
 * entity has not been modified since a specific time
 * @property string $max_forwards Limit the number of times the message
 * can be forwarded through proxies or gateways
 * @property string $origin Initiates a request for cross-origin resource
 * sharing
 * @property string $pragma Implementation-specific headers that may have
 * various effects anywhere along the request-response chain
 * @property string $proxy_authorization Authorization credentials for
 * connecting to a proxy
 * @property string $range Request only part of an entity. Bytes are
 * numbered from 0
 * @property string $referer This is the address of the previous web page
 * from which a link to the currently requested page was followed. (The word
 * "referrer" has been misspelled in the RFC as well as in most implementations
 * to the point that it has become standard usage and is considered correct
 * terminology)
 * @property string $te The transfer encodings the user agent is willing
 * to accept
 * @property string $user_agent The user agent string of the user agent
 * @property string $upgrade Ask the server to upgrade to another protocol
 * @property string $via Informs the server of proxies through which the
 * request was sent
 * @property string $warning A general warning about possible problems
 * with the entity body
 */
class HeaderList implements \ArrayAccess {

private $headerArray = [];

public function __construct($serverArray) {
	foreach ($serverArray as $key => $value) {
		if(strpos($key, "HTTP_") !== 0) {
			continue;
		}

		$headerName = str_replace("_", "-", substr($key, 5));
		$headerName = strtolower($headerName);
		$this->headerArray[$headerName] = $value;
	}
}

public function offsetExists($offset) {
	$offset = strtolower($offset);
	return isset($this->headerArray[$offset]);
}

public function offsetGet($offset) {
	$offset = strtolower($offset);
	return $this->headerArray[$offset];
}

public function offsetSet($offset, $value) {
	throw new \Gt\Core\Exception\InvalidAccessException();
}

public function offsetUnset($offset) {
	throw new \Gt\Core\Exception\InvalidAccessException();
}

/**
 * @param string $name Header name
 *
 * @return string Header value
 */
public function __get($name) {
	$name = strtolower($name);

	$name = str_replace("_", "-", $name);
	return $this->headerArray[$name];
}

}#