<?php
namespace Test\App\Page\Dir\NestedNoIndex;
use Gt\WebEngine\Logic\Page;

class _DynamicPagePage extends Page {
	public function go() {
		$t = $this->document->getTemplate("script-name");
		$t->bindKeyValue("script-name", "Nested Dynamic Page");
		$t->insertTemplate();

		$this->document->bindKeyValue(
			"dynamic-page",
			$this->dynamicPath->get("dynamicPage")
		);
	}
}