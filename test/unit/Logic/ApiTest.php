<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Input\InputData\Datum\InputDatum;
use Gt\Session\Session;
use Gt\WebEngine\Logic\AbstractLogic;
use Gt\WebEngine\Logic\Api;
use Gt\WebEngine\Logic\DynamicPath;
use Gt\WebEngine\Refactor\ObjectDocument;
use Iterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class ApiTest extends TestCase {
	public function testBeforeAfterGo() {
		$viewModel = self::createMock(ObjectDocument::class);
		$config = self::createMock(Config::class);
		$server = self::createMock(ServerInfo::class);
		$input = self::createMock(Input::class);
		$cookie = self::createMock(CookieHandler::class);
		$session = self::createMock(Session::class);
		$database = self::createMock(Database::class);
		$dynamicPath = self::createMock(DynamicPath::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
		];

		$testClass = new class(...$args) extends Api {};

		self::assertIsCallable([$testClass, "before"]);
		$testClass->before();
		self::assertIsCallable([$testClass, "go"]);
		$testClass->go();
		self::assertIsCallable([$testClass, "after"]);
		$testClass->after();
	}

	public function testHandleDo() {
		$viewModel = self::createMock(ObjectDocument::class);
		$config = self::createMock(Config::class);
		$server = self::createMock(ServerInfo::class);
		$input = self::createMock(Input::class);
		$cookie = self::createMock(CookieHandler::class);
		$session = self::createMock(Session::class);
		$database = self::createMock(Database::class);
		$dynamicPath = self::createMock(DynamicPath::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
		];

		$testClass = new class(...$args) extends Api {
			public $testObj;

			function testSetupInputData($testData) {
				$this->input = $testData;
				$this->testObj = new StdClass();
				$this->testObj->doneSomething = false;
			}

			function doSomething() {
				$this->testObj->doneSomething = true;
			}
		};

		$testData = new class implements Iterator {
			private $data = [
				"name" => "none",
				"do" => "something",
			];
			private $iteratorKey = 0;

			public function current() {
				$keys = array_keys($this->data);
				$key = $keys[$this->iteratorKey];
				return $this->data[$key];
			}

			public function next() {
				$this->iteratorKey++;
			}

			public function key() {
				$keys = array_keys($this->data);
				return $keys[$this->iteratorKey];
			}

			public function valid() {
				$keys = array_keys($this->data);
				$key = $keys[$this->iteratorKey] ?? null;
				return isset($this->data[$key]);
			}

			public function rewind() {
				$this->iteratorKey = 0;
			}

			public function do() {
				$caller = new class {
					function call($callable) {
						$callable();
					}
				};
				return $caller;
			}
		};

		$testClass->testSetupInputData($testData);
		$testClass->handleDo();
		self::assertTrue($testClass->testObj->doneSomething);
	}
}