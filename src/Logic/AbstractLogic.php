<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Cookie\CookieHandler;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;

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
	/** @var DynamicPath */
	protected $dynamicPath;

	public function __construct(
		$viewModel,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookieHandler,
		Session $session,
		DynamicPath $dynamicPath
	) {
// $viewModel must be stored by this class's concrete constructors, as each type of Logic class
// will have its own type and implementation.
		$this->config = $config;
		$this->server = $serverInfo;
		$this->input = $input;
		$this->cookie = $cookieHandler;
		$this->session = $session;
		$this->dynamicPath = $dynamicPath;
	}

	abstract public function go();

	protected function getDynamicPathParameter(string $parameter):?string {
		return $this->dynamicPath->get($parameter);
	}
}