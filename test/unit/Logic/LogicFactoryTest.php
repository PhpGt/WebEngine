<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Http\Uri;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\Logic\Api;
use Gt\WebEngine\Logic\Autoloader;
use Gt\WebEngine\Logic\LogicFactory;
use Gt\WebEngine\Logic\Page;
use Gt\WebEngine\View\ApiView;
use Gt\WebEngine\View\PageView;
use Gt\WebEngine\View\View;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class LogicFactoryTest extends TestCase {
	/** @var Config|MockObject */
	private $config;
	/** @var ServerInfo|MockObject */
	private $serverInfo;
	/** @var Input|MockObject */
	private $input;
	/** @var CookieHandler|MockObject */
	private $cookie;
	/** @var Session|MockObject */
	private $session;
	/** @var Database|MockObject */
	private $database;
	/** @var View|MockObject */
	private $view;

	public function setUp():void {
		$this->config = self::createMock(Config::class);
		$this->serverInfo = self::createMock(ServerInfo::class);
		$this->input = self::createMock(Input::class);
		$this->cookie = self::createMock(CookieHandler::class);
		$this->session = self::createMock(Session::class);
		$this->database = self::createMock(Database::class);
		$this->view = self::createMock(View::class);
	}

	public function testCreatePageLogicFromPathApi() {
		$appNamespace = "\\Test\\App";
		$projectRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"autoloading",
		]);
		$baseDirectory = $projectRoot . "/api";
		$path = $projectRoot . "/api/service-thing.php";
		/** @var MockObject|UriInterface $uri */
		$uri = self::createMock(Uri::class);

		require_once($path);

		$this->view = self::createMock(ApiView::class);

		$sut = new LogicFactory();
		$this->setMocks($sut);
		$logic = $sut->createPageLogicFromPath(
			$path,
			$appNamespace,
			$baseDirectory,
			$uri
		);

		self::assertInstanceOf(Api::class, $logic);
	}

	public function testCreatePageLogicFromPathPage() {
		$appNamespace = "\\Test\\App";
		$projectRoot = implode(DIRECTORY_SEPARATOR, [
			__DIR__,
			"..",
			"..",
			"project",
			"autoloading",
		]);
		$baseDirectory = $projectRoot . "/page";
		$path = $projectRoot . "/page/dir/index.php";
		/** @var MockObject|UriInterface $uri */
		$uri = self::createMock(Uri::class);

		require_once($path);

		$this->view = self::createMock(PageView::class);

		$sut = new LogicFactory();
		$this->setMocks($sut);
		$logic = $sut->createPageLogicFromPath(
			$path,
			$appNamespace,
			$baseDirectory,
			$uri
		);

		self::assertInstanceOf(Page::class, $logic);
	}

	private function setMocks(LogicFactory $factory) {
		$factory->setConfig($this->config);
		$factory->setServerInfo($this->serverInfo);
		$factory->setInput($this->input);
		$factory->setCookieHandler($this->cookie);
		$factory->setSession($this->session);
		$factory->setDatabase($this->database);
		$factory->setView($this->view);
	}
}