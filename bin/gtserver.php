#!/usr/bin/env php
<?php
/**
 * Used as a wrapper to the PHP built-in server to handle directory paths and
 * alert the developer if directories do not exist, before starting the server.
 *
 * This script should be executed from the base directory of the PHP.Gt
 * application wishing to be served (the "approot"), either by referencing the
 * script absolutely, or by having it within the user's environment path.
 * Alternatively, the base directory of the PHP.Gt application (the "gtroot")
 * can be passed as the --approot argument.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Core;
final class GtServer {

private 
	$gtroot,
	$approot,
	$port = 8080,

	// Used by getopt for parsing cli arguments.
	// See php.net/manual/en/function.getopt.php
	$opt_char = [
		"no-value" => "",
		"required" => ":",
		"optional" => "::",
	],

	// Define each option and associated short-option character(s):
	$opts = [
		"no-value" => [
			"help" => [
				"shortopt" => "h",
				"description" => "Displays this help message",
			],
		],
		"required" => [
			// No required arguments.
		],
		"optional" => [
			"approot" => [
				"shortopt" => "a",
				"description" => "Application root directory",
				"value" => null,
			],
			"port" => [
				"shortopt" => "p",
				"description" => "Port to bind webserver on",
				"value" => 8080,
			],
		],
	],
$_private;

public function __construct() {
	$this->gtroot = dirname(__DIR__);
	$this->approot = getcwd();

	// Only automatically run when being executed from cli, to allow testing.
	if(php_sapi_name() === "cli") {
		$this->getOptions();
		$this->run();
	}
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

/**
 * Runs the PHP process with built-in server mode enabled.
 */
public function run() {
	$fp = popen(
		"php -S localhost:{$this->port} "
		. "-t {$this->approot}/www "
		. "{$this->gtroot}/Core/Router.php "
		// Redirect STDOUT to this process's STDIN.
		. "1>&0", 
		"r"
	);

	if(!is_resource($fp)) {
		echo "ERROR: Could not open php built-in server.\n";
		exit(1);
	}

	// Pass back all output from newly-spawned process.
	while(false !== ($s = fread($fp, 1024)) ) {
		echo $s;
	}

	pclose($fp);	
}

}#

// This script is intended to be run from CLI, so a self-instantiating class is
// required. When not run from CLI, the constructor does not call inner methods.
// This allows for proper unit testing.
return new GtServer;