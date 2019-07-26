<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;

abstract class CommonApi extends Api {
	/** @var CommonLogicPropertyStore */
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
		CommonLogicPropertyStore $logicProperty
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