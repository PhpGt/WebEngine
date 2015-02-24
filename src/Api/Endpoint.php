<?php
/**
 * An endpoint refers to the whole API request, including any data parameters
 * to the request. An Endpoint object is used to contain the script used to
 * interact with the data source and also the API response as a Payload.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Api;

use \Gt\Core\Path;

class Endpoint {

const SCRIPT_TYPE_SQL = "script_type_sql";
const SCRIPT_TYPE_LOGIC = "script_type_logic";

private $path;
private $subPath;
private $params;
private $api;

private $scriptPath;
private $scriptNamespace;
private $scriptType;

/**
 * @param string $path The absolute path to the API version directory on disk
 * @param string $subPath The enpoint's path to use
 * @param array $params Associative array of parameters to use on this endpoint
 * @param Api $api The Api used to access the Endpoint
 */
public function __construct($path, $subPath, $params, $api) {
	$this->path = $path;
	$this->subPath = $subPath;
	$this->params = $params;
	$this->api = $api;

	$this->scriptPath = $this->getScriptPath();
	$this->loadScript();
}

/**
 * $this->path and $this->subPath are used to build a full-path-on-disk, but
 * at this point we do not know if the basename of the path is a file within a
 * directory, or a method within a class.
 *
 * If the endpoint is completed with a method in a script, the method will be
 * included in the return value. Alternatively, if the script is an ApiLogic
 * class, the go() method will be called on the class, otherwise if the script
 * is SQL it will be processed automatically as defined in the config.
 *
 * @return string The absolute path to the script file used in this endpoint,
 * with file extension and optional method name. If a method name is required,
 * the method will be placed after two colons (i.e. /path/to/file.php::myMethod)
 */
private function getScriptPath() {
	$method = "";

	$fullPath = $this->path . "/" . $this->subPath;
	$fullPath = Path::fixCase($fullPath);

	// $actionName is the name of the API action being done. This may be the
	// name of a script, or the name of a method within a Logic class.
	$actionName = basename($fullPath);
	$ext = "";
	$containerPath = dirname($fullPath);

	// $containerPath may be a directory containing the $actionName script, or
	// a Logic class containing the $actionName as a method.
	if(is_dir($containerPath)) {
		foreach (new \DirectoryIterator($containerPath) as $fileInfo) {
			if($fileInfo->isDot()) {
				continue;
			}

			$pathInfo = pathinfo($fileInfo->getPathname());

			if($pathInfo["filename"] === $actionName) {
				$ext = $pathInfo["extension"];
				// TODO: Flag that the action is a script file.
				break;
			}
		}
	}
	else {
		// TODO: Handle Logic class with $actionName method.
		// $method = "somethingHere";
	}

	$return = "$fullPath.$ext";

	if(!empty($method)) {
		$return .= "::$method";
	}

	return $return;
}

private function loadScript() {
	$pathInfo = pathinfo(strtok($this->scriptPath, ":"));
	$this->scriptMethod = strtok(":");


	switch ($pathInfo["extension"]) {
	case "php":
		$this->scriptType = self::SCRIPT_TYPE_LOGIC;
		// With no method passed in, the path must be to an API Logic class
		// with a go() method.
		if(empty($this->scriptMethod)) {
			$this->scriptMethod = "go";
		}

		// Check class exists within autoloader (no need to load it yet).
		$namespace = Path::getNamespace($this->scriptPath);
		if(!class_exists($namespace, true)) {
			throw new LogicNotFoundException($namespace);
		}

		$this->scriptNamespace = $namespace;
		$this->script = new $namespace(
			$this->api,
			$this->api->responseContent,
			$this->api->session,
			$this->api->data
		);

		if(!$this->script instanceof Logic) {
			throw new InvalidLogicTypeException(gettype($this->script));
		}

		break;

	case "sql":
		$this->scriptType = self::SCRIPT_TYPE_SQL;
		throw new \Gt\Core\Exception\NotImplementedException();
		break;

	default:
		throw new InvalidScriptTypeException($pathInfo["extension"]);
		break;
	}
}

/**
 * Executes the script(s) associated with this Endpoint, similarly to how the
 * ApiDispatcher works.
 *
 * If the script is an SQL file, it will create a new Database object as a DAL,
 * otherwise the ApiLogic will be interfaced directly.
 *
 * TODO: Execute _common ApiLogic files!
 */
public function execute() {
	$returnValue = null;

	switch($this->scriptType) {
	case self::SCRIPT_TYPE_LOGIC:
		$method = $this->scriptMethod;
		$this->script->setParams($this->params);
		$returnValue = $this->script->$method();
		// TODO: Execute _common ApiLogic files!
		break;

	case self::SCRIPT_TYPE_SQL:
		break;
	}

	return $returnValue;
}

}#