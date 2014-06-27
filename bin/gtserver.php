#!/usr/bin/env php
<?php
/**
 * This script should be executed from the base directory of the PHP.Gt
 * application wishing to be served (the "approot"), either by referencing the
 * script absolutely, or by having it within the user's environment path.
 * Alternatively, the base directory of the PHP.Gt application (the "gtroot")
 * can be passed as the --approot argument.
 */
$opt_char = [
	"no-value" => "",
	"required" => ":",
	"optional" => "::",
];

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
