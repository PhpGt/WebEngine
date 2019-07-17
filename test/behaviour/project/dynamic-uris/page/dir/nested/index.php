<?php
namespace Test\App\Page\Dir\Nested;
use Gt\WebEngine\Logic\Page;

class IndexPage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Nested Index");
		$t->insertTemplate();
	}
}