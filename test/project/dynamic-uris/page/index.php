<?php
namespace Test\App\Page;
use Gt\WebEngine\Logic\Page;

class IndexPage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Base Index");
		$t->insertTemplate();
	}
}