<?php
/**
 * The ApiElement object acts as the wrapper to the API object itself. A wrapper
 * is necessary because API objects for each DAL table collection are not
 * required to issue queries to the database. This could mean that an API class
 * does not exist for a particular table collection, so in that case a generic 
 * API class will be instantiated by this class.
 */
final class ApiElement {
	private $_apiName;
	private $_apiObject = null;
	private $_dal = null;

	/**
	 * Called by the ApiWrapper when accessed as an array. 
	 *
	 * @param String $name The name of the table collection.
	 * @param Dal $dal The current DAL object.
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
	 * When the API element calls a method, the method's details are stored in
	 * the API object's properties. This is done so that if there is no API
	 * class for the current table collection, the API object can execute the
	 * correct DAL queries in the default way.
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