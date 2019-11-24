<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\Header\Headers;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\Refactor\ObjectDocument;

abstract class Api extends AbstractLogic {
	/** @var ObjectDocument */
	protected $document;

	public function __construct(
		ObjectDocument $viewModel,
		Config $config,
		ServerInfo $serverInfo,
		Input $input,
		CookieHandler $cookieHandler,
		Session $session,
		Database $database,
		DynamicPath $dynamicPath,
		Headers $headers
	) {
		$this->document = $viewModel;

		parent::__construct(
			$viewModel,
			$config,
			$serverInfo,
			$input,
			$cookieHandler,
			$session,
			$database,
			$dynamicPath,
			$headers
		);
	}
}