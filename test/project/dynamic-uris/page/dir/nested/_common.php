<?php
namespace Test\App\Page\Dir\Nested;
use Gt\WebEngine\Logic\Page;

class _CommonPage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Nested Common");
		$t->insertTemplate();
	}
}