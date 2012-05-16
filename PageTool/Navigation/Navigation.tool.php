<?php
/**
 * TODO: Docs.
 * Simply adds a class of "selected" to the relevant "body nav li".
 */
class Navigation_PageTool extends PageTool {
	private $_navElements;

	public function go($api, $dom, $template, $tool) {
		$this->_navElements = $dom["nav"];

		foreach($this->_navElements as $nav) {
			$navLiTags = $nav["ul li, ol li"];
			foreach($navLiTags as $li) {
				$dir = DIR;
				if(strstr($dir, "/")) {
					$dir = substr($dir, 0, strpos($dir, "/"));
				}

				$toMatch = strlen($dir) > 0
					? $dir
					: FILE;
				$pattern = "/" . $toMatch . "/";

				if (strstr(DIR, "/")) {
					//var_dump($pattern, FILE);die();
				}

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