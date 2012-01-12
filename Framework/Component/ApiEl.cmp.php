<?php
/**
 * TODO: Docs.
 */
final class ApiElement {
	private $_apiObject = null;
	private $_dal = null;

	/**
	 * TODO: Docs.
	 */
	public function __construct($name, $dal) {
		$className = $name . "_Api";
		
		$apiFileArray = array(
			APPROOT . DS . "Api" . DS . $name . ".api.php",
			GTROOT  . DS . "Api" . DS . $name . ".api.php"
		);
		foreach ($apiFileArray as $apiFile) {
			if(file_exists($apiFile)) {
				require_once($apiFile);
				break;
			}
		}

		if(class_exists($className)) {
			$this->_apiObject = new $className();
		}

		$this->_dal = $dal;
	}

	/**
	 * TODO: Docs.
	 */
	public function __call($name, $args) {
		if(!method_exists($this->_apiObject, $name)) {
			// TODO: Throw error when method doesn't exist.
			return false;
		}

		$paramArray = array($this->_dal);
		$paramArray = array_merge($paramArray, $args);

		return call_user_func_array(
			array($this->_apiObject, $name),
			$paramArray
		);
	}
}
?>