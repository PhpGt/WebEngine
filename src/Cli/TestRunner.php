<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Cli;

class TestRunner {

const TYPE_ALL = "type_all";
const TYPE_UNIT = "type_unit";
const TYPE_ACCEPTANCE = "type_acceptance";

const ERRORLEVEL_UNIT = 1;
const ERRORLEVEL_ACCEPTANCE = 2;

public function __construct($applicationRoot, $type) {
	$type = strtolower("TYPE_" . $type);

	switch ($type) {
	case self::TYPE_ALL:
		$unitResult = $this->testUnit();
		$acceptanceResult = $this->testAcceptance();
		break;

	case self::TYPE_UNIT:
		$unitResult = $this->testUnit();
		break;

	case self::TYPE_ACCEPTANCE:
		$acceptanceResult = $this->testAcceptance();
		break;

	default:
		throw new InvalidTestRunnerTypeException($type);
		break;
	}

	$errorLevel = $unitResult | $acceptanceResult;
	$errorLevel = 3;
	if($errorLevel == 0) {
		echo "\n ✓ SUCCESS : All tests passing.";
	}
	else {
		echo "\n ✗ FAILURE : ";

		if($errorLevel & self::ERRORLEVEL_UNIT
		&& $errorLevel & self::ERRORLEVEL_ACCEPTANCE) {
			echo "Both unit and acceptance";

		}
		else if($errorLevel & self::ERRORLEVEL_UNIT) {
			echo "Acceptance tests passed, but unit";
		}
		else if($errorLevel & self::ERRORLEVEL_ACCEPTANCE) {
			echo "Unit tests passed, but acceptance";
		}

		echo " tests failed.";
	}

	echo "\n\n";
	exit($errorLevel);
}

private function testUnit() {

}

private function testAcceptance() {

}

}#