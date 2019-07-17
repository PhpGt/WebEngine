<?php
namespace Test\App\Page\Dir;
use Gt\WebEngine\Logic\Page;

class IndexPage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Dir Index");
		$t->insertTemplate();
	}
}