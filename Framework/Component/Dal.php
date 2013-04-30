<?php class Dal implements ArrayAccess {
/**
 * The DAL object is a wrapper to the actual DAL provided by PHP's PDO class.
 * There is no direct way of calling the DAL's methods, as this is done through
 * the API class (the API class can perform manipulation on the data provided
 * directly from the DAL).
 *
 * Handled in this class is the connection settings to the MySQL database, and
 * the automatic database deployment and just-in-time table creation.
 *
 * ALPHATODO:
 * TODO: createTableAndDependencies needs optimising, plus there are too many
 * bugs at the moment.
 */
private $_config = null;
private $_dbh = null;
private $_dalElArray = array();
public $_dbDeploy = null;

/**
 * Only ever called internally. Stores the required settings for when the
 * connection is made.
 * For efficiency, the connection is not made until it needs to be used.
 */
public function __construct($config) {
	$this->_config = $config;
	if(!isset($_SESSION["DbDeploy_TableCache"])) {
		$_SESSION["DbDeploy_TableCache"] = array();
	}
}

/**
 * Called at the end of each page request. Setting a PDO object to null
 * is all that is required to allow PHP's garbage collector to deallocate
 * the resource, however if there are any advancements in the database
 * connector, they may need extra destruction actions.
 */
public function __destruct() {
	$this->_dbh = null;
}

/**
 * Creates a new database connection with the settings provided to the
 * constructor. At the moment, it is only possible to connect to one database
 * per application.
 */
public function connect() {
	if(!is_null($this->_dbh)) {
		return;
	}
	try {
		$this->_dbh = new PDO(
			$this->_config["ConnectionString"],
			$this->_config["Username"],
			$this->_config["Password"]
		);
		$this->_dbh->setAttribute(
			PDO::ATTR_ERRMODE,
			PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e) {
		// TODO: Proper error handling.
		// In development mode, show help message to how to create database.
		// Output SQL to create database and all users.
		$this->autoDeploy($e);
	}
}

/**
 * The Dal object implements ArrayAccess, so that each created DalElement
 * can be cached into an associative array. This means that during a single
 * request, only one DalElement of the same type is required to be
 * constructed.
 * 
 * The function always returns true, because if the offset does not exist,
 * it will be automatically created.
 */
public function offsetExists($offset) {
	// First, check cache to see if DalObject already exists.
	if(array_key_exists($offset, $this->_dalElArray)) {
		return true;
	}

	$this->_dalElArray[$offset] = new DalEl(
		$this,
		$offset
	);

	return true;
}

/**
 * Gets the cached DalElement from the Dal object's internal array cache.
 * Providing an offset that doesn't exist will cause the offset to be
 * automatically created (from within offsetExists method).
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
 * Setting the DalElement cache is not allowed.
 */
public function offsetSet($offset, $value) {}

/**
 * Unsetting the DalElement cache is not allowed.
 */
public function offsetUnset($offset) {}

/**
 * Returns the value of the last inserted primary key, null if not set.
 */
public function lastInsertId() {
	return $this->_dbh->lastInsertId();
}

/**
 * Prepares an SQL statement, ready to have variables injected and to be
 * executed later.
 */
public function prepare($sql, $config = null) {
	return $this->_dbh->prepare($sql);
}

/**
 * When an SQL error is thrown, this function will attempt to fix the error.
 * This will be done silently on production applications, but will display
 * the progress when debugging.
 *
 * Usual errors are non-existent table errors, where this method will
 * automatically attempt to create the tables and all dependencies.
 */
public function autoDeploy($input) {
	$message = "";
	if($input instanceof PDOException) {
		$message = $input->getMessage();
	}
	else if($input instanceof PDOStatement) {
		$message = $input->errorInfo();
		$message = $message[2];
	}
	// Grab the statement's error message.
	$patternArray = array(
		"NO_TABLE" => "/Table '(.*)' doesn't exist/",
		"NO_PROCEDURE" => "/PROCEDURE (.*) does not exist/",
		"NO_USER" => "/Access denied for user '(.*)'@/",
		"NO_DB" => "/Unknown database '(.*)'/"
	);

	$data = array("Type" => null, "Match" => null);
	$match = array();
	// Find known error messages.
	foreach ($patternArray as $patternName => $pattern) {
		if(preg_match($pattern, $message, $match) > 0) {
			$data = array(
				"Type" => $patternName,
				"Match" => $match
			);
		}
	}

	// Store the automatic deployment details for outputting later.
	$this->_dbDeploy = new StdClass();
	$this->_dbDeploy->type = $data["Type"];
	$this->_dbDeploy->match = $data["Match"];
	$this->_dbDeploy->message = $message;
	$this->_dbDeploy->tableCollectionsDeployed = array();
	$this->_dbDeploy->tableCollectionsFailed = array();
	$this->_dbDeploy->tablesDeployed = array();
	$this->_dbDeploy->tablesFailed = array();
	$this->_dbDeploy->tablesSkipped = array();

	switch($data["Type"]) {
	case "NO_TABLE":
	case "NO_PROCEDURE":
		$this->_dbh->query("SET FOREIGN_KEY_CHECKS = 0;");
		// Function to create given table, but also create any 
		// tables that are dependant - recursively.
		$tableName = substr($data["Match"][1],
			strrpos($data["Match"][1], ".") + 1);
		if($this->createTableAndDependencies($tableName) === true) {
			$this->_dbDeploy->tableCollectionsDeployed[] = $tableName;
		}
		else {
			$this->_dbDeploy->tableCollectionsFailed[] = $tableName;
		}
		$this->_dbh->query("SET foreign_key_checks = 1");
		break;
	case "NO_DB":
	case "NO_USER":
		$dbName = $data["Match"][1];
		$sqlPath = GTROOT . "/Framework";
		if(!is_dir($sqlPath)) {
			// TODO: Throw proper error at this point.
			die("ERROR: Invalid framework directory structure!");
		}

		$rootPass = isset($_POST["RootPass"])
			? $_POST["RootPass"]
			: null;
		
		if(empty($rootPass)) {
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
		$this->_dbDeploy->error = "Error in SQL.";
		throw new HttpError(500, $message);
		break;
	}
}

/**
 * Supplying a name of a table, or sub-table within a table collection will
 * cause this method to deploy the table automatically, along with all the
 * dependencies detected within the creation query.
 *
 * All tables should be named using PHP.Gt conventions to work fully.
 */
public function createTableAndDependencies($tableName) {
	if(empty($tableName)) {
		return;
	}
	// Table Collections should have the name of their base table at the start
	// of their name, for instance BaseTable_SubTable. This makes it easy to
	// find what Table Collection to deploy from any contained table.
	if(strstr($tableName, "_")) {
		$baseTable = substr($tableName, 0, strpos($tableName, "_"));
		if(!in_array($baseTable, $_SESSION["DbDeploy_TableCache"])) {
			$this->createTableAndDependencies($baseTable);
		}
	}

	// Only proceed if table doesn't already exist.
	//if(!in_array($tableName, $_SESSION["DbDeploy_TableCache"])) {
		$stmt = $this->_dbh->prepare("
			select `TABLE_NAME`
			from `information_schema`.`TABLES`
			where `TABLE_SCHEMA` = :TableName
		");
		$stmt->execute([":TableName" => $tableName]);
		$dbResult = $stmt->fetch();
		if($dbResult === false) {
			// There is no table created already.
			// Attempt to find creating script for given table.
			$sqlPathArray = array(
				GTROOT  . "/Database/" . ucfirst($tableName),
				APPROOT . "/Database/" . ucfirst($tableName),
				// Check PageTools:
				GTROOT  . "/PageTool/" . ucfirst($tableName) . "/Database",
				APPROOT . "/PageTool/" . ucfirst($tableName) . "/Database",
			);

			foreach ($sqlPathArray as $sqlPath) {
				if(!is_dir($sqlPath)) {
					continue;
				}
				$fileArray = scanDir($sqlPath);
				foreach ($fileArray as $file) {
					// All creation scripts begin with an underscore.
					if($file[0] !== "_") {
						continue;
					}

					$sql = file_get_contents("$sqlPath/$file");

					// Detect any table references in SQL and attempt
					// to create them first.
					$matches = array();
					$pattern = "/REFERENCES\s*`([^`]+)`/i";
					preg_match_all($pattern, $sql, $matches);

					foreach ($matches[1] as $dep) {
						if(!in_array(
						$dep, $_SESSION["DbDeploy_TableCache"])) {
							// Recursively call this function for each
							// dependency, ignoring any already completed.
							$_SESSION["DbDeploy_TableCache"][] = $tableName;
							$this->createTableAndDependencies($dep);
						}
					}

					// Execute the creation/insertion script.
					$result = $this->_dbh->query($sql);
					if($result === false) {
						$this->_dbDeploy->tablesFailed[] = $dep;
						$this->_dbDeploy->error = $this->_dbh->errorInfo();
						// TODO: Throw proper error.
						die($this->_dbh->errorInfo());
						exit;
					}
					else {
						$this->_dbDeploy->tablesDeployed[] = $tableName;
						$_SESSION["DbDeploy_TableCache"][] = $tableName;
					}
				}
			}
		}
		else {
			$_SESSION["DbDeploy_TableCache"][] = $tableName;
			$this->_dbDeploy->tablesSkipped[] = $tableName;
			return;
		}
	//}

	// TODO: Doesn't seem to be setting cookie...
	// Output the dbDeploy status as JSON into a session cookie.
	setcookie("PhpGt_DbDeploy",
		json_encode($this->_dbDeploy), 
		0, 
		"/"
	);
}

}#