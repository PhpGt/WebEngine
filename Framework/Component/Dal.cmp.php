<?php
/**
 * TODO: Docs.
 */
class Dal implements ArrayAccess {
	private $_dbh = null;
	private $_dalElArray = array();
	private $_paramChar = null;

	/**
	 * TODO: Docs.
	 */
	public function __construct($config) {
		try {
			$this->_dbh = new PDO(
				$config["ConnectionString"],
				$config["Username"],
				$config["Password"]
			);

			$this->_paramChar = $config["ParamChar"];
		}
		catch (PDOException $e) {
			// TODO: Proper error handling.
			// In development mode, show help message to how to create database.
			// Output SQL to create database and all users.
			$this->fixError($e, $config);
		}
	}

	/**
	 * TODO: Docs.
	 */
	public function __destruct() {
		$this->_dbh = null;
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetExists($offset) {
		// First, check cache to see if DalObject already exists.
		if(array_key_exists($offset, $this->_dalElArray)) {
			return true;
		}

		$this->_dalElArray[$offset] = new DalElement(
			$this,
			$offset,
			$this->_paramChar
		);

		return true;
	}
	
	/**
	 * TODO: Docs.
	 */
	public function offsetGet($offset) {
		$offset = ucfirst($offset);
		if(!$this->offsetExists($offset)) {
			// TODO: Proper error handling - DalObject doesn't exist.
			return null;
		}
		
		return $this->_dalElArray[$offset];
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetSet($offset, $value) {
	}

	/**
	 * TODO: Docs.
	 */
	public function offsetUnset($offset) {
	}

	/**
	 * TODO: Docs.
	 */
	public function lastInsertId() {
		return $this->_dbh->lastInsertId();
	}

	/**
	 * TODO: Docs.
	 */
	public function prepare($sql, $config = null) {
		return $this->_dbh->prepare($sql);
	}

	/**
	 * TODO: Docs.
	 */
	public function fixError($input) {
		$message = "";
		if($input instanceof PDOException) {
			$message = $input->getMessage();
		}
		else if($input instanceof PDOStatement) {
			$message = $input->errorInfo();
		}
		// Grab the statment's error message.
		$patternArray = array(
			"NO_TABLE" => "/^Table '(.*)' doesn't exist/",
			"NO_DB" => "/Unknown database '(.*)'/"
		);

		$data = array();
		// Find known error messages.
		foreach ($patternArray as $patternName => $pattern) {
			if(preg_match($pattern, $message, &$data) > 0) {
				$data = array(
					"Type" => $patternName,
					"Match" => $data
				);
			}
		}
		
		switch($data["Type"]) {
		case "NO_TABLE":
			// Attempt to find creation script for given table.
			$tableName = substr($data["Match"][1],
				strrpos($data["Match"][1], ".") + 1);
			$sqlPathArray = array(
				APPROOT . DS . "Database" . DS . ucfirst($tableName),
				GTROOT  . DS . "Database" . DS . ucfirst($tableName)
			);

			foreach($sqlPathArray as $sqlPath) {
				// Look for underscore prefixed files.
				if(!is_dir($sqlPath)) {
					continue;
				}
				$dh = opendir($sqlPath);
				while(false !== $file = readdir($dh)) {
					if($file[0] !== "_") {
						continue;
					}

					echo "Executing $file." . PHP_EOL;
					$sql = file_get_contents($sqlPath . DS . $file);
					$result = $this->_dbh->query($sql);

					if($result === false) {
						// TODO: Throw proper error.
						die("Error in auto-deployment stage.");
					}
				}
				closedir($dh);
			}
			break;
		case "NO_DB":
			$dbName = $data["Match"][1];
			$sqlPath = GTROOT . DS . "Framework";
			if(!is_dir($sqlPath)) {
				die("ERROR: Invalid framework directory structure!");
			}

			$rootPass = isset($_POST["RootPass"])
				? $_POST["RootPass"]
				: null;
			
			if(is_null($rootPass)) {
				header("Location: /Gt?DbDeploy=" . $_SERVER["REQUEST_URI"]);
				exit;
			}

			$dh = opendir($sqlPath);

			while(false !== $file = readdir($dh)) {
				if($file[0] !== "_") {
					continue;
				}
			}

			closedir($dh);

			break;
		default:
			break;
		}

		die();
	}
}
?>