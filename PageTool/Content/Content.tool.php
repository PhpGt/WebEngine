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
			$content = $api["Content"]->get(array("Name" => $element->id));
			if(!empty($content["Value"])) {
				$element->innerHTML = $content["Value"];
			}
		}
	}
}
?>