<?php
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Dom\HTMLDocument;
use Gt\DomTemplate\DocumentBinder;
use Gt\Http\ResponseStatusException\ResponseStatusException;
use Gt\Http\Uri;
use Gt\ServiceContainer\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class ErrorRequestHandler extends RequestHandler {
	public function __construct(
		Config $config,
		callable $finishCallback,
		private Throwable $throwable,
		protected Container $serviceContainer,
	) {
		parent::__construct($config, $finishCallback);
	}

	public function handle(
		ServerRequestInterface $request
	):ResponseInterface {
		$errorCode = 500;
		if($this->throwable instanceof ResponseStatusException) {
			$errorCode = $this->throwable->getHttpCode();
		}

		$errorUri = new Uri("/_$errorCode");
		$errorRequest = $request->withUri($errorUri);

		$this->completeRequestHandling($errorRequest);
		$this->response = $this->response->withStatus($errorCode);
		return $this->response;
	}
}
