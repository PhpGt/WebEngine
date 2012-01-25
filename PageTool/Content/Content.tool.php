<?php
/**
 * TODO: Docs.
 * Editable content blocks in web pages.
 */
class Content_PageTool extends PageTool {
	private $_elements = null;

	public function main($api, $dom, $template) {
		$this->_elements = $dom["*[@data-editable]"];
		foreach ($this->_elements as $element) {
			$element->innerHTML = "<p>test</p>";
		}
	}
}
?>