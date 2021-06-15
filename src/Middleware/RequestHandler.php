<?php
namespace Gt\WebEngine\Middleware;

use Gt\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The RequestHandler takes the current Request and
 *
 * The RequestHandler must be provided with a representation of global state,
 * through the $globals constructor parameter. Within a WebEngine application,
 * global state is prohibited. Instead, the RequestHandler dispatches the
 * appropriate object-oriented alternatives to the application, where
 * appropriate, to promote encapsulation and aid development maintenance.
 *
 * @link https://www.php.gt/webengine/globals
 */
class RequestHandler implements RequestHandlerInterface {
	public function __construct(
		private array $globals
	) {
	}

	public function handle(
		ServerRequestInterface $request
	):ResponseInterface {
		return new Response();
	}
}
