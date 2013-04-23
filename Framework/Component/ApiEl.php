<?php final class ApiEl {
/**
 * The ApiElement object acts as the wrapper to the API object itself. A wrapper
 * is necessary because API objects for each DAL table collection are not
 * required to issue queries to the database. This could mean that an API class
 * does not exist for a particular table collection, so in that case a generic 
 * API class will be instantiated by this class.
 */
private $_apiName;
private $_apiObject = null;
private $_dal = null;

private $_isTool = false;

/**
 * Called by the ApiWrapper when accessed as an array. 
 *
 * @param String $name The name of the table collection.
 * @param Dal $dal The current DAL object.
 */
public function __construct($name, $dal) {
	if(strstr($name, "_PageTool")) {
		$name = substr($name, 0, strrpos($name, "_"));
		$this->_isTool = true;
	}

	$this->_apiName = $name;
	$className = $name . "_Api";
	
	$apiFileArray = array(
		APPROOT . "/Api/$name.api.php",
		GTROOT  . "/Api/$name.api.php",
	);

	if($this->_isTool) {
		$toolApiFileArray = array(
			APPROOT . "/PageTool/$name/Api/$name.api.php",
			GTROOT  . "/PageTool/$name/Api/$name.api.php"
		);
		$apiFileArray = array_merge($toolApiFileArray, $apiFileArray);
	}

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
	
	if($this->_isTool) {
		$this->_apiObject->setTool();
	}
	
	if(call_user_func_array(
		array($this->_apiObject, "apiCall"),
		array($this->_dal)
	)) {
		return $this->_apiObject->getDalResult();
	}
}

public function getName() {
	return $this->_apiName;
}

}?>