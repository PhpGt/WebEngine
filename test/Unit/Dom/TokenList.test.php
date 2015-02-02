<?php
/**
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2015 Bright Flair Ltd. (http://brightflair.com)
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
namespace Gt\Dom;

class DomTokenList_Test extends \PHPUnit_Framework_TestCase {

private $html = '<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Dom Test</title>
</head>
<body>

<h1>Dom Test!</h1>

</body>
</html>';

private $document;
private $classNameArray = [
	"my-class",
	"secondaryClass",
	"and-another",
];

public function setUp() {
	$this->document = new Document($this->html);
}

public function testClassListPropertyExists() {
	$h1 = $this->document->getElementsByTagName("h1")[0];
	$this->assertInstanceOf("\Gt\Dom\TokenList", $h1->classList);
}

public function testClassListContainsOneClass() {
	$h1 = $this->document->getElementsByTagName("h1")[0];
	$h1->setAttribute("class", $this->classNameArray[0]);

	$this->assertTrue($h1->classList->contains($this->classNameArray[0]));
}

public function testClassListContainsMultipleClasses() {
	$h1 = $this->document->getElementsByTagName("h1")[0];
	$h1->setAttribute("class", implode(" ", $this->classNameArray));

	foreach ($this->classNameArray as $className) {
		$this->assertTrue($h1->classList->contains($className));
	}
}

public function testClassListAdds() {
	$h1 = $this->document->getElementsByTagName("h1")[0];

	for($i = 0, $count = count($this->classNameArray); $i < $count; $i++) {
		$className = $this->classNameArray[$i];
		$this->assertFalse($h1->classList->contains($className));
		$h1->classList->add($className);
		$this->assertTrue($h1->classList->contains($className));
	}

	foreach ($this->classNameArray as $className) {
		$this->assertTrue($h1->classList->contains($className));
	}
}

}#