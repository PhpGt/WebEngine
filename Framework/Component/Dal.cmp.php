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
			//var_dump($message);die();
			$message = $message[2];
		}
		// Grab the statment's error message.
		$patternArray = array(
			"NO_TABLE" => "/Table '(.*)' doesn't exist/",
			"NO_USER" => "/Access denied for user '(.*)'@/",
			"NO_DB" => "/Unknown database '(.*)'/"
		);

		$data = array("Type" => null, "Match" => null);
		$match = array();
		// Find known error messages.
		foreach ($patternArray as $patternName => $pattern) {
			if(preg_match($pattern, $message, &$match) > 0) {
				$data = array(
					"Type" => $patternName,
					"Match" => $match
				);
			}
		}

		var_dump($data["Type"]);
		var_dump($message);
		
		switch($data["Type"]) {
		case "NO_TABLE":
			// Function to create given table, but also create any 
			// tables that are dependant - recursively.
			$tableName = substr($data["Match"][1],
				strrpos($data["Match"][1], ".") + 1);
			if(empty($tableName)) {
				// TODO: Throw proper error at this point.
				die("Error: Table cannot be created. $tableName");
			}
			$this->createTableAndDependencies($tableName);
			break;
		case "NO_DB":
		case "NO_USER":
			$dbName = $data["Match"][1];
			$sqlPath = GTROOT . DS . "Framework";
			if(!is_dir($sqlPath)) {
				die("ERROR: Invalid framework directory structure!");
			}

			$rootPass = isset($_POST["RootPass"])
				? $_POST["RootPass"]
				: null;
			
			if(is_null($rootPass)) {
				header("Location: /Gt.html?DbDeploy="
					. $_SERVER["REQUEST_URI"]);
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
	}

	/**
	 * TODO: Docs.
	 */
	public function createTableAndDependencies($tableName) {
		// Attempt to find creation script for given table.
		$sqlPathArray = array(
			APPROOT . DS . "Database" . DS . ucfirst($tableName),
			GTROOT  . DS . "Database" . DS . ucfirst($tableName)
		);
		
		foreach($sqlPathArray as $sqlPath) {
			// Look for underscore prefixed files.
			if(!is_dir($sqlPath)) {
				continue;
			}
			$fileArray = scandir($sqlPath);
			foreach ($fileArray as $file) {
				// All creation scripts begin with an underscore. Skip the
				// files that don't.
				if($file[0] !== "_") {
					continue;
				}

				echo "<p>Deploying $file.";
				$sql = file_get_contents($sqlPath . DS . $file);
				var_dump($sql);

				// Detect any table references in SQL and attempt to create
				// them too.
				/*
				 constraint `Fk_User__User_Type`
					foreign key (`Fk_User_Type`)
					references `User_Type` (`Id`)
					on delete restrict
					on update cascade,
				*/
				$matches = array();
				$pattern = "/REFERENCES\s*`([^`]+)`/i";
				preg_match_all($pattern, $sql, $matches);
				
				foreach ($matches[1] as $dependency) {
					echo "<p>DEPENDENCY DETECTED IN '$tableName': $dependency.";
				}
				die();

				$result = $this->_dbh->query($sql);

				if($result === false) {
					var_dump($this->_dbh->errorInfo());
					exit;
				}
				else {
					echo " - SUCCESS!";
				}
				echo "</p>";
			}
		}

		echo "<p>Automatic deployment successful! "
			. "<a href='" . $_SERVER["REQUEST_URI"] . "'>Continue</a></p>";
		var_dump($sqlPathArray);
	}
}
?>