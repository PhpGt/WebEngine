<?php class Api {
/**
 * Api is a dependency injector for each module of the application's data layer.
 * By default, the API links directly to the DAL class, and if standard queries
 * are executed, there is no requirement for an API class to be created.
 * 
 * API classes are to be used when there are high levels of computation needed,
 * or external data-sources (non-mysql) required, such as external webservices
 * or file-based data structures.
 */
private $_errorMessage 	= null;
private $_apiName 		= null;
private $_methodName 	= null;
private $_methodParams 	= null;
private $_result 		= null;
private $_dalResult		= null;

private $_affectedRows	= 0;
private $_lastInsertId	= null;

private $_isTool		= false;

protected $externalMethods = array();

public function __construct() {
}

/**
 * Called by the dispatcher when a JSON request is made to an API. This
 * function calls the API's correct method and passes in the given DAL and
 * the stored method parameters.
 * @param Dal $dal The current response DAL.
 * @return bool True on success, false on failure.
 */
public function apiCall($dal) {
	// Quit early if API is a special error class.
	if($this->_apiName == "PhpGt_API_Error") {
		return true;
	}

	// Check to see if there is a defined method of this API's method name.
	if(method_exists($this, $this->_methodName)) {
		$dalEl = new DalEl($dal, $this->_apiName, $this->_isTool);
		$params = array_merge(
			array($this->_methodParams),
			array($dal),
			array($dalEl)
		);

		// The DalResult object comes from the DalEl's query function.
		try {
			$this->_dalResult = call_user_func_array(
				array($this, $this->_methodName),
				$params
			);

			$this->_result = array();

			if((is_array($this->_dalResult) 
			|| $this->_dalResult instanceof Traversable)) {
				foreach($this->_dalResult as $key => $value) {
					$this->_result[$key] = $value;
				}				
			}
			return true;
		}
		catch(PDOException $e) {
			$this->setError($e->getMessage());
		}
	}
	
	if(in_array(ucfirst($this->_methodName), $this->externalMethods)
	|| strtolower(EXT) !== "json") {
		// If there is no defined method, execute the SQL and pass in the
		// parameters directly.
		// Only allow json calls to execute SQL if the script's name is
		// contained within the externalMethods array (if not json, allow
		// anyway as in that case it will be being called internally).
		$dalElement = $dal[$this->_apiName];
		if($this->_isTool) {
			$dalElement->setTool();
		}

		try {
			$this->_dalResult = call_user_func_array(
				array($dalElement, $this->_methodName),
				array($this->_methodParams)
			);

			if(!$this->_dalResult) {
				return $this->_dalResult;
			}

			$this->_result = array();
			foreach ($this->_dalResult as $key => $value) {
				$this->_result[$key] = $value;
			}

			$this->_affectedRows = $this->_dalResult->affectedRows;
			$this->_lastInsertID = $this->_dalResult->lastInsertID;
			return true;
		}
		catch(PDOException $e) {
			$this->setError($e->getMessage());
		}
	}
	return false;
}

public function apiOutput() {
	$json = new StdClass();

	if(!empty($this->_errorMessage)) {
		$json->error = $this->_errorMessage;
	}
	$json->method = new StdClass();
	$json->method->name = $this->_methodName;
	$json->method->params = $this->_methodParams;
	$json->result = $this->_result;
	$json->affectedRows = $this->_affectedRows;
	$json->lastInsertID = $this->_lastInsertID != 0
		? $this->_lastInsertID
		: null;

	echo json_encode($json);
}

/**
 * Executes the API's given method name with the given parameters, and
 * saves the resulting object.
 * @return bool True on success, false on failure.
 */
public function execute() {
	$this->_result = call_user_func_array(
		array($this, $this->_methodName),
		$this->_methodParams);
	
	return !is_null($this->_result);
}

public function setTool() {
	$this->_isTool = true;
}

public function getDalResult() {
	return $this->_dalResult;
}

public function setError($message) {
	$this->_errorMessage = $message;
}

public function setApiName($name) {
	$this->_apiName = $name;
}

public function setMethodName($name) {
	$this->_methodName = $name;
}

public function setMethodParams($array) {
	$this->_methodParams = $array;
}

}#