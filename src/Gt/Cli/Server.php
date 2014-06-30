<?php
/**
 * Used as a wrapper to the PHP built-in server to handle directory paths and
 * alert the developer if directories do not exist, before starting the server.
 *
 * This script should be executed from the base directory of the PHP.Gt
 * application wishing to be served (the "approot"), either by referencing the
 * script absolutely, or by having it within the user's environment path.
 * Alternatively, the base directory can be passed as the --approot argument.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
final class Server {

private $gtroot;
private $approot;
private $port = 8080;

private $process;

public function __construct(Arguments $arguments) {
	$this->gtroot = dirname(__DIR__);
	$this->approot = getcwd();

	// Pass all provided arguments to call function to handle.
	foreach ($arguments as $key => $value) {
		$this->call($key, $value);
	}

	$this->process = new Process();
	$this->process->run();
}

/**
 * Handles user input after parsing cli arguments.
 * A key-value-pair is passed as the two parameters to the method. 
 * 
 * @param string $name The argument name, passed via cli.
 * @param string $value The argument value, passed via cli.
 */
public function call($name, $value) {
	switch($name) {

	case "help":	// Output a list of all possible commands.
		echo "\nUsage: gtserver [-a|--approot=$APPROOT] [-p|--port=$PORT]\n\n";
		
		foreach($this->opts as $opt_type => $opt_array) {
			foreach($opt_array as $opt_name => $opt_detail) {
				$str = "\t--$opt_name";
				foreach (str_split($opt_detail["shortopt"]) as $opt_char) {
					$str .= ", -$opt_char";
				}

				$str = str_pad($str, 24, " ");
				$str .= $opt_detail["description"];
				echo $str . "\n";
			}
		}
		echo "\nFor support, see "
			. "https://github.com/BrightFlair/PHP.Gt/wiki/built-in-server";
		echo "\n\n";
		exit;
		break;

	case "approot":		// Set the application directory root.
		if(!is_dir($value)) {
			echo "ERROR: Provided approot is not a directory.\n";
			exit(1);
		}
		$this->approot = $value;
		break;

	case "port":		// Set the port used by the server.
		if(!ctype_digit($value)
		|| $value < 1024
		|| $value > 65535) {
			echo "ERROR: Provided port is not a valid.\n";
			exit(1);
		}
		$this->port = $value;
		break;

	default:
		break;
	}
}

public function getOptions() {
	// Characters used to build command line options.
	// See php.net/manual/en/function.getopt.php

	// Build opts_long and opts_short for passing to getopt function below.
	$opts_long = [];
	$opts_short = "";
	foreach($this->opts as $opt_type => $opt_array) {
		foreach ($opt_array as $opt_name => $opt_detail) {
			$opts_long[] = $opt_name . $this->opt_char[$opt_type];
			$opts_short .= implode(
				$this->opt_char[$opt_type],
				str_split($opt_detail["shortopt"])
			) . $this->opt_char[$opt_type];
		}
	}
	$options = getopt($opts_short, $opts_long);

	// Act on passed arguments:
	foreach($this->opts as $opt_type => $opt_array) {
		foreach ($opt_array as $opt_name => $opt_detail) {
			$argValue = null;

			if(isset($options[$opt_name])) {
				$argValue = $options[$opt_name];
			}
			else {
				foreach (str_split($opt_detail["shortopt"]) as $opt_char) {
					if(isset($options[$opt_char])) {
						$argValue = $options[$opt_char];
					}
				}
			}

			if(!is_null($argValue)) {
				$this->call($opt_name, $argValue);
			}
		}
	}
}

}#