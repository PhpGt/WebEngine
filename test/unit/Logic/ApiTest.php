<?php
namespace Gt\WebEngine\Test\Logic;

use Gt\Http\Header\Headers;
use Gt\WebEngine\HttpException\HttpSeeOther;
use PHPUnit\Framework\TestCase;
use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\Logic\Api;
use Gt\WebEngine\Logic\DynamicPath;
use Gt\WebEngine\Refactor\ObjectDocument;
use Gt\WebEngine\Test\Helper\FunctionOverride\Override;
use Iterator;
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
		$headers = self::createMock(Headers::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
			$headers,
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
		$headers = self::createMock(Headers::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
			$headers,
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

	/** @runInSeparateProcess  */
	public function testReload() {
		$viewModel = self::createMock(ObjectDocument::class);
		$config = self::createMock(Config::class);
		$server = self::createMock(ServerInfo::class);
		$input = self::createMock(Input::class);
		$cookie = self::createMock(CookieHandler::class);
		$session = self::createMock(Session::class);
		$database = self::createMock(Database::class);
		$dynamicPath = self::createMock(DynamicPath::class);
		$headers = self::createMock(Headers::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
			$headers,
		];

		$sut = new class(...$args) extends Api {
			function doTestReload() {
				$this->reload();
			}
		};

		self::expectException(HttpSeeOther::class);
		$sut->doTestReload();
	}

	public function testGetDynamicPathParameter() {
		$expectedPathParam = uniqid();

		$viewModel = self::createMock(ObjectDocument::class);
		$config = self::createMock(Config::class);
		$server = self::createMock(ServerInfo::class);
		$input = self::createMock(Input::class);
		$cookie = self::createMock(CookieHandler::class);
		$session = self::createMock(Session::class);
		$database = self::createMock(Database::class);
		$dynamicPath = self::createMock(DynamicPath::class);
		$dynamicPath->method("get")
			->with("testParam")
			->willReturn($expectedPathParam);
		$headers = self::createMock(Headers::class);

		$args = [
			$viewModel,
			$config,
			$server,
			$input,
			$cookie,
			$session,
			$database,
			$dynamicPath,
			$headers,
		];

		$sut = new class(...$args) extends Api {
			function doTestDynamicParam($testObj) {
				$testObj->message = $this->getDynamicPathParameter("testParam");
			}
		};

		$testObj = new StdClass();
		$testObj->message = null;
		$sut->doTestDynamicParam($testObj);

		self::assertEquals($expectedPathParam, $testObj->message);
	}
}