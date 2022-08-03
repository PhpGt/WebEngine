<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\ConfigFactory;
use Gt\Config\ConfigSection;
use Gt\Http\RequestFactory;
use Gt\Http\StatusCode;
use Gt\Logger\Log;
use Gt\WebEngine\Debug\Timer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The fundamental purpose of any PHP framework is to provide a mechanism for
 * generating an HTTP response for an incoming HTTP request. Because this is
 * such a common requirement, the PHP Framework Interop Group have specified a
 * "PHP standards recommendation" (PSR) to help define the expected contract
 * between the components of a web framework. The PSR that defines the common
 * interfaces for HTTP server request handlers is PSR-15.
 *
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-15-request-handlers.md
 *
 * This Lifecycle class implements the PSR-15 MiddlewareInterface, which defines
 * a single "process" function that takes a Request and a RequestHandler,
 * and in turn returns a Response.
 *
 * @link https://github.com/PhpGt/WebEngine/wiki/HTTP-Middleware
 *
 * At the start of the lifecycle, when using an ordinary HTTP server such as
 * Apache or Nginx, no Request objects are available yet:
 * that's the job of the "start" function. It will create an appropriate
 * Request object and pass it to the "process" function for handling.
 *
 * An optional execution would be to use a PHP-based HTTP server that provides
 * its own Request object, and pass it directly to the "process" function.
 */
class Lifecycle implements MiddlewareInterface {
	private Timer $timer;

	public function start():void {
//		set_error_handler(function($errno, $errstr, $errfile, $errline) {
//			throw new \Exception($errstr, $errno, 0, $errfile, $errline);
//		}, E_WARNING);
// The first thing that's done within the WebEngine lifecycle is start a timer.
// This timer is only used again at the end of the call, when finish() is
// called - at which point the entire duration of the request is logged out (and
// slow requests are highlighted as a NOTICE).
		$this->timer = new Timer();

// Starting the output buffer is done before any logic is executed, so any calls
// to any area of code will not accidentally send output to the client.
		ob_start();

// A PSR-7 HTTP Request object is created from the current global state, ready
// for processing by the Handler.
		$requestFactory = new RequestFactory();
		$request = $requestFactory->createServerRequestFromGlobalState(
			$_SERVER,
			$_FILES,
			$_GET,
			$_POST,
		);

// The handler is an individual component that processes a request and produces
// a response, as defined by PSR-7. It's where all your application's logic is
// executed - the brain of WebEngine. Here we pass in a reference to the finish
// function, so the RequestHandler can complete the request early if needed.
		$handler = new RequestHandler(
			ConfigFactory::createForProject(
				getcwd(),
				"vendor/phpgt/webengine/config.default.ini"
			),
			$this->finish(...),
		);

// The request and request handler are passed to the PSR-15 process function,
// which will return our PSR-7 HTTP Response.
		$response = $this->process($request, $handler);

// Now we can finish the HTTP lifecycle by providing the HTTP response for
// outputting to the browser, along with the buffer so we can display the
// contents in a debug area.
		$this->finish(
			$response,
			$handler->getConfigSection("app"),
		);
	}

	/**
	 * Process an incoming server request and return a response,
	 * delegating response creation to a handler.
	 */
	public function process(
		ServerRequestInterface $request,
		RequestHandlerInterface $handler,
	):ResponseInterface {
		return $handler->handle($request);
	}

	public function finish(
		ResponseInterface $response,
		ConfigSection $appConfig
	):never {
		$buffer = trim(ob_get_clean());
		http_response_code($response->getStatusCode() ?? StatusCode::OK);

		foreach($response->getHeaders() as $key => $value) {
			$stringValue = implode(", ", $value);
			header("$key: $stringValue", true);
		}

		if(strlen($buffer) > 0) {
			if(str_contains($buffer, "\n")) {
				$buffer = "\n$buffer";
			}
			Log::debug("Logic output: $buffer");
		}

		$renderBufferSize = $appConfig->getInt("render_buffer_size");
		$body = $response->getBody();
		$body->rewind();
		ob_start();
		while(!$body->eof()) {
			echo $body->read($renderBufferSize);
			ob_flush();
			flush();
		}

// The very last thing that's done before the script ends is to stop the Timer,
// so we know exactly how long the request-response lifecycle has taken.
		$this->timer->stop();
		$delta = number_format($this->timer->getDelta(), 2);
		if($delta >= $appConfig->getFloat("slow_delta")) {
			Log::warning("Lifecycle end with VERY SLOW delta time: $delta seconds. https://www.php.gt/webengine/slow-delta");
		}
		elseif($delta >= $appConfig->getFloat("very_slow_delta")) {
			Log::notice("Lifecycle end with SLOW delta time: $delta seconds. https://www.php.gt/webengine/slow-delta");
		}
		else {
			Log::debug("Lifecycle end, delta time: $delta seconds.");
		}

		exit;
	}
}
