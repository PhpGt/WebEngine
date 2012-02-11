<?php
/**
 * TODO: Docs.
 * Simply adds a class of "selected" to the relevant "body nav li".
 */
class Navigation_PageTool extends PageTool {
	private $_navElements;

	public function go($api, $dom, $template) {
		$this->_navElements = $dom["nav"];

		foreach($this->_navElements as $nav) {
			$navLiTags = $nav["ul li"];
			foreach($navLiTags as $li) {
				$toMatch = strlen(DIR) > 0
					? DIR
					: FILE;
				$pattern = "/" . $toMatch . "/";

				if(FILE === "Index") {
					// Match "Index" with "/".
					$pattern = "/^" . DIR . "\/$|Index/";
				}

				if($li->hasAttribute("data-selected-pattern")) {
					$pattern = $li->getAttribute("data-selected-pattern");
				}
				
				// Match the current URL with the anchor's href.
				$href = $li["a"]->getAttribute("href");
				if(preg_match($pattern, $href) > 0) {
					$li->addClass("selected");
				}
			}
		}
	}
}
?>