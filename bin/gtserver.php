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

$GTROOT = dirname(__DIR__);
$APPROOT = getcwd();
$PORT = 8080;

function arg($name, $value) {
	global $APPROOT;
	global $PORT;
	global $opts;

	switch($name) {
	case "help":
		echo "\nUsage: gtserver [-a|--approot=$APPROOT] [-p|--port=$PORT]\n\n";
		foreach($opts as $opt_type => $opt_array) {
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
	case "approot":
		if(!is_dir($value)) {
			echo "ERROR: Provided approot is not a directory.\n";
			exit(1);
		}
		$APPROOT = $value;
		break;
	case "port":
		if(!ctype_digit($value)
		|| $value < 1024
		|| $value > 65535) {
			echo "ERROR: Provided port is not a valid.\n";
			exit(1);
		}
		$PORT = $value;
		break;
	default:
		break;
	}
}

// Characters used to build command line options.
// See php.net/manual/en/function.getopt.php
$opt_char = [
	"no-value" => "",
	"required" => ":",
	"optional" => "::",
];

// Define each option and associated short-option character(s):
$opts = [
	"no-value" => [
		"help" => [
			"shortopt" => "h",
			"description" => "Displays this help message",
		],
	],
	"required" => [
	],
	"optional" => [
		"approot" => [
			"shortopt" => "a",
			"description" => "Application root directory",
			"value" => getcwd(),
		],
		"port" => [
			"shortopt" => "p",
			"description" => "Port to bind webserver on",
			"value" => 8080,
		],
	],
];

// Build opts_long and opts_short for passing to getopt function below.
$opts_long = [];
$opts_short = "";
foreach($opts as $opt_type => $opt_array) {
	foreach ($opt_array as $opt_name => $opt_detail) {
		$opts_long[] = $opt_name . $opt_char[$opt_type];
		$opts_short .= implode(
			$opt_char[$opt_type],
			str_split($opt_detail["shortopt"])
		) . $opt_char[$opt_type];
	}
}
$options = getopt($opts_short, $opts_long);

// Act on passed arguments:
foreach($opts as $opt_type => $opt_array) {
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
			arg($opt_name, $argValue);
		}
	}
}

$fp = popen(
	"php -S localhost:$PORT -t $APPROOT/www $GTROOT/Core/Router.php 1>&0", 
	"r"
);

if(!is_resource($fp)) {
	echo "ERROR: Could not open php built-in server.\n";
	exit(1);
}

while(false !== ($s = fread($fp, 1024)) ) {
	echo $s;
}

pclose($fp);