<?php
/**
 * TODO: Docs.
 */
final class ApiElement {
	private $_apiName;
	private $_apiObject = null;
	private $_dal = null;

	/**
	 * TODO: Docs.
	 */
	public function __construct($name, $dal) {
		$this->_apiName = $name;
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
		else {
			$this->_apiObject = new Api();
		}

		$this->_dal = $dal;
	}

	/**
	 * TODO: Docs.
	 */
	public function __call($methodName, $params) {
		$this->_apiObject->setApiName($this->_apiName);
		$this->_apiObject->setMethodName($methodName);
		$this->_apiObject->setMethodParams($params);
		
		if(call_user_func_array(
			array($this->_apiObject, "apiCall"),
			array($this->_dal)
		)) {
			return $this->_apiObject->getDalResult();
		}
	}
}
?>