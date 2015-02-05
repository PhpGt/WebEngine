<?php
/**
 * Client side compilation is handled by third party libraries that must have
 * full test-suites associated. This test case is intended to test how PHP.Gt
 * interfaces with these libraries, rather than the functionality of the
 * libraries themselves.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\ClientSide;

use \scssc as ScssParser;

class Compiler_Test extends \PHPUnit_Framework_TestCase {

private $tmp;

public function setUp() {
	$this->tmp = \Gt\Test\Helper::createTmpDir();
}

public function tearDown() {
	\Gt\Test\Helper::cleanup($this->tmp);
}

public function testCompilesScss() {
	$filePath = $this->tmp . "/file.scss";
	$source = '$red: rgb(225, 16, 32); a { color: $red; }';
	file_put_contents($filePath, $source);
	// Regular expressions used to ignore white space.
	$output = preg_replace("/\s/", "", Compiler::parse($filePath));
	$expected = preg_replace("/\s/", "", "a { color: #e11020;}");

	$this->assertEquals($expected, $output);
}

public function testCompilesScssWithRelativeUris() {
	$filePathChild = $this->tmp . "/inc/child.scss";
	$sourceChild = "\$col_bg: yellow";
	if(!is_dir(dirname($filePathChild)) ) {
		mkdir(dirname($filePathChild), 0775, true);
	}
	file_put_contents($filePathChild, $sourceChild);

	$filePathBase = $this->tmp . "/file.scss";
	$sourceBase = "@import \"inc/child.scss\";\n"
		. "body { background: \$col_bg; }";
	file_put_contents($filePathBase, $sourceBase);

	$output = Compiler::parse($filePathBase);
	$expected = "background: yellow";

	$this->assertContains($expected, $output);
}

/**
 * @expectedException \Gt\ClientSide\CompilerParseException
 */
public function testInvalidSyntaxThrowsException() {
	$source = "body { { background: \$bad_syntax!; }";
	$filePath = $this->tmp . "/file.scss";
	file_put_contents($filePath, $source);

	$output = Compiler::parse($filePath);
}

}#