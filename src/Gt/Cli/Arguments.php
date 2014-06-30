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
class Arguments implements \ArrayAccess, \Iterator {

// List holds an associative array to all passed-in arguments and their values.
public $list = [];
private $iteratorIndex = 0;

// Used by getopt for parsing cli arguments.
// See php.net/manual/en/function.getopt.php
private $opt_char = [
	"no-value" => "",
	"required" => ":",
	"optional" => "::",
];
private $opt_short = "";
private $opt_long = [];

// Define each option and associated short-option character(s):
private $options = [
	// Arrays must match this pattern:
	"no-value" => [ 
	/*
		"argumentname" => [
			"shortopt" => "a", // list of characters, in string form.
			"description" => "Help message associated with argument",
			"value" => "test", // optional default value.
		]
	*/
	],
	"required" => [],
	"optional" => [],
];

/**
 * The constructor automatically builds the public $list variable from either
 * the invoking script's arguments, or the passed-in $list array.
 * 
 * @param array $options list of allowed arguments, matching the pattern
 * described in private $options variable comments.
 * @param array $list OPTIONAL list of override arguments.
 */
public function __construct(array $options, array $list = null) {
	if (!is_null($list)) {
		$this->list = $list;
		return;
	}

	$this->options = $options;

	$this->build();
	$this->parse();
}

/**
 * Build the long and short parameters as required by the getopt function.
 * Short argument parameters are stored in a string, long argument parameters
 * are stored in an array. See getopt documentation for more information:
 * php.net/manual/en/function.getopt.php#refsect1-function.getopt-parameters
 */
private function build() {
	// Loop over each allowed option type to build short and long option list.
	foreach ($this->options as $opt_type => $opt_array) {
		foreach ($opt_array as $opt_name => $opt_detail) {
			$this->opt_long []= $opt_name . $this->opt_char[$opt_type];
			$this->opt_short .= implode(
				$this->opt_char[$opt_type],
				str_split($opt_detail["shortopt"])
			) . $this->opt_char[$opt_type];
		}
	}
}

/**
 * Parses the command line interface arguments, using the generated short and
 * long options, and stores the result in the public $list array.
 * 
 * $list will always be an associative array of full-length keys, even if the
 * shortoptions were used.
 */
private function parse() {
	$gotopt = getopt($this->opt_short, $this->opt_long);

	foreach ($this->options as $opt_type => $opt_array) {
		foreach ($opt_array as $opt_name => $opt_detail) {
			$argValue = null;

			if (isset($gotopt[$opt_name])) {
				$argValue = $gotopt[$opt_name];
			}
			else {
				foreach (str_split($opt_detail["shortopt"]) as $opt_char) {
					if (isset($gotopt[$opt_char])) {
						$argValue = $gotopt[$opt_char];
					}
				}
			}

			if(!is_null($argValue)) {
				$this->list[$opt_name] = $argValue;				
			}
		}
	}
}

public function offsetExists($offset) {
	return isset($this->list[$offset]);
}

public function offsetGet($offset) {
	return $this->list[$offset];
}

public function offsetSet($offset, $value) {
	throw new Gt\Exception\UnsupportedOperationException(
		"Trying to set the read-only arguments list.");
}

public function offsetUnset($offset) {
	throw new Gt\Exception\UnsupportedOperationException(
		"Trying to unset the read-only arguments list.");
}

public function current() {
	return current($this->list);
}

public function key() {
	return key($this->list);
}

public function next() {
	return next($this->list);
}

public function rewind() {
	return reset($this->list);
}

public function valid() {
	return false !== current($this->list);
}
}#