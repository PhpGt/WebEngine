<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\Cookie;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\View\View;
use Psr\Http\Message\UriInterface;
use TypeError;

class LogicFactory {
	protected static $config;
	protected static $serverInfo;
	protected static $input;
	protected static $cookie;
	protected static $session;
	protected static $database;

	/** @var View */
	protected static $view;

	public static function setConfig(Config $config):void {
		self::$config = $config;
	}

	public static function setServerInfo(ServerInfo $serverInfo):void {
		self::$serverInfo = $serverInfo;
	}

	public static function setInput(Input $input):void {
		self::$input = $input;
	}

	public static function setCookieHandler(CookieHandler $cookie):void {
		self::$cookie = $cookie;
	}

	public static function setSession(Session $session):void {
		self::$session = $session;
	}

	public static function setDatabase(Database $database):void {
		self::$database = $database;
	}

	public static function setView(View $view):void {
		self::$view = $view;
	}

	public static function createPageLogicFromPath(
		string $path,
		string $appNamespace,
		string $baseDirectory,
		UriInterface $uri
	):Page {
		$className = self::getLogicClassFromPath(
			$path,
			$appNamespace,
			"Page",
			$baseDirectory
		);

		$dynamicPathParameters = self::getDynamicPathParameters(
			$path,
			$baseDirectory,
			$uri
		);

		try {
			/** @var Page $class */
			$class = new $className(
				self::$view->getViewModel(),
				self::$config,
				self::$serverInfo,
				self::$input,
				self::$cookie,
				self::$session,
				self::$database,
				$dynamicPathParameters
			);

		}
		catch(TypeError $exception) {
			throw new InvalidLogicConstructorParameters($exception->getMessage());
		}

		return $class;

	}

	protected static function getLogicClassFromPath(
		string $path,
		string $appNamespace,
		string $logicTypeNamespace,
		string $baseDirectory
	):string {
		$basePageNamespace = implode("\\", [
			$appNamespace,
			$logicTypeNamespace,
		]);

		$logicPathRelative = substr($path, strlen($baseDirectory));
// The relative logic path will be the filename with page directory stripped from the left.
// /app/src/page/index.php => index.php
// /app/src/api/child/directory/thing.php => child/directory/thing.php
		$className = ClassName::transformUriCharacters(
			$logicPathRelative,
			$basePageNamespace,
			$logicTypeNamespace
		);

		$className = str_replace("@", "_", $className);
		return $className;
	}

	public static function getDynamicPathParameters(
		string $path,
		string $baseDirectory,
		UriInterface $uri
	):DynamicPath {
		$uriPath = $uri->getPath();
		$relativeDirPath = str_replace(
			$baseDirectory,
			"",
			$path
		);
		$relativeDirPath = str_replace(
			DIRECTORY_SEPARATOR,
			"/",
			$relativeDirPath
		);
		$relativeDirParts = explode("/", $relativeDirPath);
		$relativeDirParts = array_filter($relativeDirParts);

		$uriParts = explode("/", $uriPath);
		$uriParts = array_filter($uriParts);

		$keyValuePairs = [];

		foreach($relativeDirParts as $i => $part) {
			$part = strtok($part, ".");
			if($part[0] !== "@") {
				continue;
			}

			$partName = substr($part, 1);
			$keyValuePairs[$partName] = $uriParts[$i];
		}

		return new DynamicPath($keyValuePairs);
	}
}