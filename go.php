<?php
/**
 * Welcome to the PHP.Gt WebEngine!
 *
 * This file is the entry point to the WebEngine. The whole request-response lifecycle is
 * documented at https://github.com/PhpGt/WebEngine/wiki/From-request-to-response
 */

/**
 * Before any code is executed, return false here if a static file is requested. When running the
 * PHP inbuilt server, this will output the static file. Other webservers should not get to this
 * point, but it's safe to prevent unnecessary execution.
 */
$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
if(is_file($_SERVER["DOCUMENT_ROOT"] . $uri)) {
	return false;
}

/**
 * Require the Composer autoloader, so the rest of the script can locate classes by their namespace,
 * rather than having to know where on disk the files exist.
 * @see https://getcomposer.org/doc/00-intro.md
 */
$composerAutoloaderPath = implode(DIRECTORY_SEPARATOR, [
	dirname($_SERVER["DOCUMENT_ROOT"]),
	"vendor",
	"autoload.php",
]);
require($composerAutoloaderPath);

/**
 * That's all we need to start the request-response lifecycle. Buckle up and enjoy the ride!
 * @see https://github.com/PhpGt/WebEngine/wiki/From-request-to-response
 */
$lifecycle = new Gt\WebEngine\Lifecycle();
$lifecycle->start();