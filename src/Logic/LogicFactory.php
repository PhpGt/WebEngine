<?php
namespace Gt\WebEngine\Logic;

use Gt\Config\Config;
use Gt\Cookie\CookieHandler;
use Gt\Database\Database;
use Gt\Http\Header\Headers;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Session\Session;
use Gt\WebEngine\FileSystem\Path;
use Gt\WebEngine\View\ApiView;
use Gt\WebEngine\View\PageView;
use Gt\WebEngine\View\View;
use Psr\Http\Message\UriInterface;

class LogicFactory {
	/** @var ?Config */
	protected $config;
	/** @var ?ServerInfo */
	protected $serverInfo;
	/** @var ?Input */
	protected $input;
	/** @var ?CookieHandler */
	protected $cookie;
	/** @var ?Session */
	protected $session;
	/** @var ?Database */
	protected $database;
	/** @var ?Headers */
	protected $headers;

	/** @var View */
	protected $view;

	public function setConfig(Config $config):void {
		$this->config = $config;
	}

	public function setServerInfo(ServerInfo $serverInfo):void {
		$this->serverInfo = $serverInfo;
	}

	public function setInput(Input $input):void {
		$this->input = $input;
	}

	public function setCookieHandler(CookieHandler $cookie):void {
		$this->cookie = $cookie;
	}

	public function setSession(Session $session):void {
		$this->session = $session;
	}

	public function setDatabase(Database $database):void {
		$this->database = $database;
	}

	public function setView(View $view):void {
		$this->view = $view;
	}

	public function setHeaders(Headers $headers):void {
		$this->headers = $headers;
	}

	public function createLogicObjectFromPath(
		string $path,
		string $appNamespace,
		string $baseDirectory,
		UriInterface $uri,
		LogicPropertyStore $commonLogicPropertyStore
	):AbstractLogic {
		$path = realpath($path);
		$baseDirectory = realpath($baseDirectory);

		$className = $this->getLogicClassFromPath(
			$path,
			$appNamespace,
			$baseDirectory
		);

		$dynamicPathParameters = $this->getDynamicPathParameters(
			$path,
			$baseDirectory,
			$uri
		);

		/** @var AbstractLogic $class */
		$class = new $className(
			$this->view->getViewModel(),
			$this->config,
			$this->serverInfo,
			$this->input,
			$this->cookie,
			$this->session,
			$this->database,
			$dynamicPathParameters,
			$this->headers,
			$commonLogicPropertyStore
		);

		return $class;
	}

	public function getDynamicPathParameters(
		string $absolutePath,
		string $baseDirectory,
		UriInterface $uri
	):DynamicPath {
		$uriPath = $uri->getPath();
		$relativeDirPath = str_replace(
			$baseDirectory,
			"",
			$absolutePath
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

		if(!Path::isDynamic($absolutePath)
		&& is_dir($absolutePath)) {
			$uriParts []= "index";
		}

		$keyValuePairs = [];
		foreach($relativeDirParts as $i => $part) {
			$part = strtok($part, ".");
			if($part[0] !== "@") {
				continue;
			}

			$partName = substr($part, 1);

			if(isset($uriParts[$i])) {
				$keyValuePairs[$partName] = $uriParts[$i];
			}
		}

		return new DynamicPath($keyValuePairs);
	}

	protected function getLogicClassFromPath(
		string $path,
		string $appNamespace,
		string $baseDirectory
	):string {
		$logicTypeNamespace = null;
		if($this->view instanceof ApiView) {
			$logicTypeNamespace = "Api";
		}
		if($this->view instanceof PageView) {
			$logicTypeNamespace = "Page";
		}
		$basePageNamespace = implode("\\", [
			$appNamespace,
			$logicTypeNamespace,
		]);

		$logicPathRelative = substr($path, strlen($baseDirectory));
		$fullPath = $baseDirectory . $logicPathRelative;
		if(is_dir($fullPath)) {
			$logicPathRelative .= "/index";
		}
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
}