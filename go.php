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
// TODO.

/**
 * Require the Composer autoloader, so the rest of the script can locate classes by their namespace,
 * rather than having to know where on disk the files exist.
 * @see https://getcomposer.org/doc/00-intro.md
 */
require(__DIR__ . "/vendor/autoload.php");

/**
 * The PHP superglobals are used to represent the HTTP request. $_COOKIE, $_FILES and $_SESSION are
 * used later.
 *
 * - $_SERVER includes information about the request and the HTTP headers
 * - $_GET is a parsed array of query string parameters from the request URL
 * - $_POST is a parsed array of query string parameters from the request body
 *
 * @see http://php.net/manual/en/language.variables.superglobals.php
 */
// TODO.

/**
 * Now the request is ready we can begin to dispatch it to the relevant areas of code. Buckle up
 * and enjoy the ride! Read more about the request-response lifecycle here:
 *
 * @see https://github.com/PhpGt/WebEngine/wiki/From-request-to-response
 */
// TODO.