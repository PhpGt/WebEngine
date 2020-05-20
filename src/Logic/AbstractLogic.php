<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\Header\Headers;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\HttpException\HttpFound;
use Gt\WebEngine\HttpException\HttpMovedPermanently;
use Gt\WebEngine\HttpException\HttpPermanentRedirect;
use Gt\WebEngine\HttpException\HttpSeeOther;
use Gt\WebEngine\HttpException\HttpTemporaryRedirect;

abstract class AbstractLogic {
	protected $viewModel;
	/** @var Config */
	protected $config;
	/** @var ServerInfo */
	protected $server;
	/** @var Input */
	protected $input;
	/** @var CookieHandler */
	protected $cookie;
	/** @var Session */
	protected $session;
	/** @var Database */
	protected $database;
	/** @var DynamicPath */
	protected $dynamicPath;
	/** @var Headers */
	protected $headers;

	public function __construct(
		$viewModel,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookieHandler,
		Session $session,
		Database $database,
		DynamicPath $dynamicPath,
		Headers $headers
	) {
// $viewModel must be stored by this class's concrete constructors, as each type of Logic class
// will have its own type and implementation.
		if(!$viewModel) {
			return;
		}

		$this->config = $config;
		$this->server = $serverInfo;
		$this->input = $input;
		$this->cookie = $cookieHandler;
		$this->session = $session;
		$this->database = $database;
		$this->dynamicPath = $dynamicPath;
		$this->headers = $headers;
	}

	public function before() {
		// This is not a required function, but it has been placed here
		// so IDEs can see it when extending Logic classes.
	}

	public function go() {
		// This is not a required function, but it has been placed here
		// so IDEs can see it when extending Logic classes.
	}

	public function after() {
		// This is not a required function, but it has been placed here
		// so IDEs can see it when extending Logic classes.
	}

	public function handleDo():void {
		foreach($this->input as $key => $value) {
			if($key !== "do") {
				continue;
			}

			$methodName = "do";

			preg_match_all("([^-_ ]+)", $value, $matches);
			foreach($matches[0] as $methodNamePart) {
				$methodName .= ucfirst($methodNamePart);
			}

			if(method_exists($this, $methodName)) {
				$this->input->do($value)->call([$this, $methodName]);
			}
		}
	}

	protected function reload():void {
		$this->redirect($this->server->getRequestUri());
	}

	protected function redirect(string $uri, int $code = 303):void {
		header(
			"Location: $uri",
			true,
			$code
		);

		switch($code) {
		case 301:
			$exception = HttpMovedPermanently::class;
			break;
		case 302:
			$exception = HttpFound::class;
			break;
		case 303:
			$exception = HttpSeeOther::class;
			break;
		case 307:
			$exception = HttpTemporaryRedirect::class;
			break;
		case 308:
			$exception = HttpPermanentRedirect::class;
			break;
		default:
			throw new RedirectCodeNotImplementedException($code);
		}

		throw new $exception($uri, $code);
	}

	protected function getDynamicPathParameter(string $parameter):?string {
		return $this->dynamicPath->get($parameter);
	}
}