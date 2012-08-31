<?php final class Gt_Reserved {
public function __construct($config) {
	$dbConfig = $config["Database"]->getSettings();

	if(isset($_GET["ShowSql"])) {
		$sqlArray = $this->getSqlArray($dbConfig);

		$count = count($sqlArray);
		echo "<p>As root user, please perform these $count queries:</p>";

		foreach($sqlArray as $index => $item) {
			$file = key($item);
			$sql = $item[$file];

			echo "<p class='filename'>$file</p>";
			echo "<pre>$sql</pre>";
		}
		echo "<p>When you have executed these queries, "
			. "<a href='?CheckSql=true'>Click here</a></p>";
		exit;
	}
	
	if(isset($_POST["RootPass"])) {
		// Automatically deploy the database!
		try {
			$dbh = new PDO(
				$dbConfig["ConnectionString_Root"],
				"root",
				$_POST["RootPass"]
			);
			foreach($this->getSqlArray($dbConfig) as $index => $item) {
				$file = key($item);
				$sql = $item[$file];

				$result = $dbh->query($sql);
				if($result === false) {
					// TODO: Proper error handling.
					var_dump($dbh->errorInfo());
					exit;
				}
			}
			$_SESSION["DbDeploy"]["Complete"] = true;
		}
		catch(PDOException $e) {
			die("Could not connect as root. Please check the password, "
				. "or alternatively deploy the database manually.");
		}
	}
	else if(isset($_GET["DbDeploy"])) {
		$_SESSION["DbDeploy"] = array(
			"Complete" => false,
			"Forward" => $_GET["DbDeploy"]
		);
		require(
			GTROOT . DS . "Framework" . DS . "Reserved" . DS . "Gt.html");
		exit;
	}
}

public function __destruct() {
	if(!isset($_SESSION["DbDeploy"])) {
		return;
	}
	if($_SESSION["DbDeploy"]["Complete"]) {
		$forward = $_SESSION["DbDeploy"]["Forward"];
		unset($_SESSION["DbDeploy"]);
		header("Location: {$forward}");
	}	
}

/**
 * TODO: Docs.
 */
private function getSqlArray($dbConfig) {
	$sqlArray = array();

	// Replace any placeholders in each SQL with config data.
	$replacements = array(
		":DbName" 			=> "`" . $dbConfig["DbName"] . "`",
		":UserServer" 		=> 
			"'" . $dbConfig["Username"] 
				. "'@'" . $dbConfig["Host"] . "'",
		":Password" 		=> "'" . $dbConfig["Password"] . "'",
		":DatabaseTable" 	=> "`" . $dbConfig["DbName"] . "`.*"
	);

	$dbPath = GTROOT . DS . "Database";
	$fileArray = scandir($dbPath);
	foreach($fileArray as $file) {
		if(strpos($file, "_CreateDatabase") !== 0) {
			continue;
		}

		$sql = file_get_contents($dbPath . DS . $file);

		foreach ($replacements as $key => $value) {
			$sql = str_replace($key, $value, $sql);
		}

		$sqlArray[] = array($file => $sql);
	}

	return $sqlArray;
}

}?>