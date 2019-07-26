<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\DomTemplate\HTMLDocument;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;

abstract class CommonPage extends Page {
	/** @var CommonLogicPropertyStore */
	protected $logicProperty;

	public function __construct(
		HTMLDocument $viewModel,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookieHandler,
		Session $session,
		Database $database,
		DynamicPath $dynamicPath,
		CommonLogicPropertyStore $logicProperty
	) {
		parent::__construct(
			$viewModel,
			$config,
			$serverInfo,
			$input,
			$cookieHandler,
			$session,
			$database,
			$dynamicPath
		);

		$this->logicProperty = $logicProperty;
	}
}