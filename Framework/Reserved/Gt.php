<?php
final class Gt_Reserved {
	public function __construct($config) {
		if(isset($_GET["ShowSql"])) {
			$sqlArray = array();

			$dbPath = GTROOT . DS . "Database";
			$dh = opendir($dbPath);
			while(false !== ($file = readdir($dh)) ) {
				if($file[0] !== "_") {
					continue;
				}

				$sql = file_get_contents($dbPath . DS . $file);
				$sqlArray[] = array($file => $sql);
			}
			closedir($dh);

			// Sort alphabetically:
			usort($sqlArray, function($a, $b) {
				if(key($a) == key($b)) {
					return 0;
				}

				return key($a) > key($b);
			});

			$count = count($sqlArray);
			echo "<p>As root user, please perform these $count queries:</p>";

			foreach($sqlArray as $index => $item) {
				$file = key($item);
				$sql = $item[$file];

				// Replace any placeholders in each SQL with config data.
				$dbConfig = $config["Database"]->getSettings();
				$replacements = array(
					":DbName" 			=> "`" . $dbConfig["DbName"] . "`",
					":UserServer" 		=> 
						"'" . $dbConfig["Username"] 
							. "'@'" . $dbConfig["Host"] . "'",
					":Password" 		=> "'" . $dbConfig["Password"] . "'",
					":DatabaseTable" 	=> "`" . $dbConfig["DbName"] . "`.*"
				);

				foreach ($replacements as $key => $value) {
					$sql = str_replace($key, $value, $sql);
				}

				echo "<p class='filename'>$file</p>";
				echo "<pre>$sql</pre>";
			}
			exit;
		}
		if(isset($_POST["RootPass"])) {
			// TODO: DbDeployment.
			die("todo - got the root password, securely deploy db!");
		}
		if(isset($_GET["DbDeploy"])) {
			require(
				GTROOT . DS . "Framework" . DS . "Reserved" . DS . "Gt.html");
			exit;
		}
	}
}
?>