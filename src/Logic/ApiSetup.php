<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;

abstract class ApiSetup extends Api {
	/** @var LogicPropertyStore */
	protected $logicProperty;

	public function __construct(
		$object,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookieHandler,
		Session $session,
		Database $database,
		DynamicPath $dynamicPath,
		LogicPropertyStore $logicProperty
	) {
		parent::__construct(
			$object,
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