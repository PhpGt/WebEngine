<?php
/**
 * Represents the passed-in command line interface arguments list, along with
 * a list of allowed arguments, their short version, and a description.
 * 
 * See php.net/manual/en/function.getopt.php
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;
class Arguments {

// Used by getopt for parsing cli arguments.
// See php.net/manual/en/function.getopt.php
private $opt_char = [
	"no-value" => "",
	"required" => ":",
	"optional" => "::",
];

// Define each option and associated short-option character(s):
private $opts = [
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
];

/**
 * The constructor automatically builds the public $list variable from either
 * the invoking script's arguments, or the passed-in $list array.
 * 
 * @param array $list OPTIONAL list of override arguments.
 */
public function __construct(array $list = null) {
	if(!is_null($list)) {
		$this->list = $list;
		return;
	}

	$this->build();
	$this->parse();
}

private function build() {

}

private function parse() {

}

}#