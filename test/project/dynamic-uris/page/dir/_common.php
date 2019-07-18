<?php
namespace Test\App\Page\Dir;
use Gt\WebEngine\Logic\Page;

class _CommonPage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Dir Common");
		$t->insertTemplate();
	}
}