<?php class ClientSideCompilerTest extends PHPUnit_Framework_TestCase {

public function setup() {
	define("GTROOT", getcwd() . "/../");
	require_once(GTROOT . "Framework/ClientSideCompiler.php");
}

}#