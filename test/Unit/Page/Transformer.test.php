<?php
/**
 * Basic tests that the functionality of transforming works. Full parsing of
 * markup languages is provided in the individual projects used.
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Page;

class Transformer_Test extends \PHPUnit_Framework_TestCase {

/**
 * @expectedException \Gt\Page\SourceNotValidException
 */
public function testUnknownSource() {
	Transformer::toHtml("unknown source", "ungabi");
}

public function testTransformerMarkdown() {
	$in = "# A heading\n\nFirst para.\n\nSecond para.";
	$out = "<h1>A heading</h1><p>First para.</p><p>Second para.</p>";

	$actual = Transformer::toHtml($in, Transformer::TYPE_MARKDOWN);
	$actual = str_replace("\n", "", $actual);
	$this->assertEquals($out, $actual);
}

}#