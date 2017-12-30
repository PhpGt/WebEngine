<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;

abstract class AbstractLogic {
	protected $viewModel;
	/** @var Config */
	protected $config;
	/** @var ServerInfo */
	protected $serverInfo;
	/** @var Input */
	protected $input;
	/** @var Cookie */
	protected $cookie;
	/** @var Session */
	protected $session;

	public function __construct(
		$viewModel,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		Cookie $cookie,
		Session $session
	) {
// $viewModel must be stored by this class's concrete constructors, as each type of Logic class
// will have its own type and implementation.
		$this->config = $config;
		$this->serverInfo = $serverInfo;
		$this->input = $input;
		$this->cookie = $cookie;
		$this->session = $session;
	}
}